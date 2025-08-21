<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\RepairHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\JobOrderImage;

class JobOrderController extends Controller
{
    /**
     * Display a listing of job orders
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);
            $search = $request->get('search');
            $status = $request->get('status');
            
            $query = JobOrder::query();
            
            // Add relationships if RepairHistory model exists
            if (class_exists('App\Models\RepairHistory')) {
                $query->with(['images', 'repairHistories' => function($q) {
                    $q->orderBy('performed_at', 'desc')->limit(3);
                }]);
            } else {
                $query->with('images');
            }
            
            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('job_order_number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhere('laptop_model', 'like', "%{$search}%");
                });
            }
            
            // Status filter
            if ($status) {
                $query->where('status', $status);
            }
            
            // Get all results without pagination for now
            $jobOrders = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json($jobOrders, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching job orders: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching job orders'], 500);
        }
    }

    /**
     * Store a newly created job order
     */
    public function store(Request $request)
    {
        Log::info('Job Order Request Data:', $request->all());

        try {
            $validatedData = $request->validate([
                'customer_type' => ['required', Rule::in(['customer', 'technician-customer'])],
                'customer_name' => 'required|string|max:255',
                'contact_number' => 'required|string',
                'customer_address' => 'nullable|string|max:255',
                'pullout_date' => 'nullable|date',
                'pulled_out_by' => [
                    'nullable',
                    Rule::requiredIf(function () use ($request) {
                        return in_array($request->status, ['Unrepairable/pullout', 'Completed/claimed']);
                    }),
                    'string',
                ],
                'laptop_model' => 'required|string',
                'status' => ['nullable', Rule::in(['Pending', 'In Progress', 'Completed', 'Unrepairable/pullout', 'Completed/claimed'])],
                'ram' => 'array',
                'ssd' => 'array',
                'hdd' => 'nullable|string',
                'has_battery' => 'boolean',
                'has_wifi_card' => 'boolean',
                'others' => 'nullable|string',
                'without' => 'nullable|string',
                'problem' => 'required|string',
                // New pricing fields - make them optional
                'repair_price' => 'nullable|numeric|min:0',
                'parts_cost' => 'nullable|numeric|min:0',
                'labor_cost' => 'nullable|numeric|min:0',
                'repair_notes' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate job order number
            $jobOrderNumber = $this->generateJobOrderNumber($validatedData['customer_type']);

            if (in_array($validatedData['status'] ?? '', ['Unrepairable/pullout', 'Completed/claimed'])) {
                $validatedData['pullout_date'] = now()->toDateString();
            }

            // Prepare data for creation
            $jobOrderData = [
                'customer_type' => $validatedData['customer_type'],
                'customer_name' => $validatedData['customer_name'],
                'customer_address' => $validatedData['customer_address'] ?? null,
                'contact_number' => $validatedData['contact_number'],
                'laptop_model' => $validatedData['laptop_model'],
                'status' => $validatedData['status'] ?? 'Pending',
                'ram' => json_encode($validatedData['ram']),
                'ssd' => json_encode($validatedData['ssd']),
                'hdd' => $validatedData['hdd'] ?? null,
                'has_battery' => $validatedData['has_battery'],
                'has_wifi_card' => $validatedData['has_wifi_card'],
                'others' => $validatedData['others'] ?? null,
                'without' => $validatedData['without'] ?? null,
                'problem' => $validatedData['problem'],
                'job_order_number' => $jobOrderNumber,
                'pullout_date' => $validatedData['pullout_date'] ?? null,
                'pulled_out_by' => $validatedData['pulled_out_by'] ?? null,
            ];

            // Add pricing fields only if the columns exist
            if ($this->checkColumnExists('job_orders', 'repair_price')) {
                $jobOrderData['repair_price'] = $validatedData['repair_price'] ?? null;
                $jobOrderData['parts_cost'] = $validatedData['parts_cost'] ?? null;
                $jobOrderData['labor_cost'] = $validatedData['labor_cost'] ?? null;
                $jobOrderData['repair_notes'] = $validatedData['repair_notes'] ?? null;
            }

            $jobOrder = JobOrder::create($jobOrderData);

            // Create repair history only if the model exists and table exists
            if (class_exists('App\Models\RepairHistory') && $this->checkTableExists('repair_histories')) {
                try {
                    RepairHistory::create([
                        'job_order_id' => $jobOrder->id,
                        'action_type' => 'diagnosis',
                        'description' => "Job order created. Problem: " . $validatedData['problem'],
                        'performed_by' => auth()->user()->name ?? 'System',
                        'performed_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Could not create repair history: ' . $e->getMessage());
                    // Continue without repair history
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Job Order Created Successfully!',
                'data' => $jobOrder
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job order creation failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Failed to create job order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified job order
     */
    public function show($id)
    {
        try {
            $jobOrder = JobOrder::with('images')->find($id);
            
            if (!$jobOrder) {
                return response()->json(['message' => 'Job Order not found'], 404);
            }

            // Load repair histories if available
            if (class_exists('App\Models\RepairHistory')) {
                $jobOrder->load(['repairHistories' => function($q) {
                    $q->orderBy('performed_at', 'desc');
                }]);
            }

            return response()->json($jobOrder, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching job order: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching job order'], 500);
        }
    }

    /**
     * Update the specified job order
     */
    public function update(Request $request, $id)
    {
        $jobOrder = JobOrder::find($id);

        if (!$jobOrder) {
            return response()->json(['message' => 'Job Order not found'], 404);
        }

        try {
            $validated = $request->validate([
                'customer_type' => ['sometimes', Rule::in(['customer', 'technician-customer'])],
                'customer_name' => 'sometimes|string|max:255',
                'laptop_model' => 'sometimes|string|max:255',
                'status' => ['sometimes', Rule::in(['Pending', 'In Progress', 'Completed', 'Unrepairable/pullout', 'Completed/claimed'])],
                'pullout_date' => 'sometimes|date',
                'pulled_out_by' => [
                    'nullable',
                    Rule::requiredIf(function () use ($request) {
                        return in_array($request->status, ['Unrepairable/pullout', 'Completed/claimed']);
                    }),
                    'string',
                ],
                'ram' => 'sometimes|array',
                'ssd' => 'sometimes|array',
                'hdd' => 'sometimes|string|max:255',
                'has_battery' => 'sometimes|boolean',
                'has_wifi_card' => 'sometimes|boolean',
                'others' => 'sometimes|string|max:500',
                'without' => 'sometimes|string|max:500',
                'repair_price' => 'sometimes|numeric|min:0',
                'parts_cost' => 'sometimes|numeric|min:0',
                'labor_cost' => 'sometimes|numeric|min:0',
                'repair_notes' => 'sometimes|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $jobOrder->status;
            
            if (isset($validated['ram'])) {
                $validated['ram'] = json_encode($validated['ram']);
            }

            if (isset($validated['ssd'])) {
                $validated['ssd'] = json_encode($validated['ssd']);
            }

            if (isset($validated['status']) && in_array($validated['status'], ['Unrepairable/pullout', 'Completed/claimed'])) {
                $validated['pullout_date'] = now()->toDateString();
            }

            // Remove pricing fields if columns don't exist
            if (!$this->checkColumnExists('job_orders', 'repair_price')) {
                unset($validated['repair_price']);
                unset($validated['parts_cost']);
                unset($validated['labor_cost']);
                unset($validated['repair_notes']);
            }

            $jobOrder->update($validated);

            // Log status change if RepairHistory exists
            if (isset($validated['status']) && $oldStatus !== $validated['status']) {
                if (class_exists('App\Models\RepairHistory') && $this->checkTableExists('repair_histories')) {
                    try {
                        RepairHistory::create([
                            'job_order_id' => $jobOrder->id,
                            'action_type' => 'note',
                            'description' => "Status changed from {$oldStatus} to {$validated['status']}",
                            'performed_by' => auth()->user()->name ?? 'System',
                            'performed_at' => now(),
                            'cost' => $validated['repair_price'] ?? null,
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('Could not create repair history: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Job Order updated successfully',
                'job_order' => $jobOrder,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job order update failed:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update job order'], 500);
        }
    }

    /**
     * Add repair history entry
     */
    public function addRepairHistory(Request $request, $id)
    {
        if (!class_exists('App\Models\RepairHistory')) {
            return response()->json(['message' => 'Repair history feature not available'], 404);
        }

        $jobOrder = JobOrder::find($id);

        if (!$jobOrder) {
            return response()->json(['message' => 'Job Order not found'], 404);
        }

        try {
            $validated = $request->validate([
                'action_type' => ['required', 'string'],
                'description' => 'required|string',
                'performed_by' => 'nullable|string',
                'cost' => 'nullable|numeric|min:0',
                'performed_at' => 'nullable|date',
            ]);

            $repairHistory = RepairHistory::create([
                'job_order_id' => $id,
                'action_type' => $validated['action_type'],
                'description' => $validated['description'],
                'performed_by' => $validated['performed_by'] ?? auth()->user()->name ?? 'System',
                'cost' => $validated['cost'] ?? null,
                'performed_at' => $validated['performed_at'] ?? now(),
            ]);

            return response()->json([
                'message' => 'Repair history added successfully',
                'data' => $repairHistory
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error adding repair history: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to add repair history'], 500);
        }
    }

    /**
     * Get repair history for a job order
     */
    public function getRepairHistory($id)
    {
        if (!class_exists('App\Models\RepairHistory')) {
            return response()->json([], 200);
        }

        $jobOrder = JobOrder::find($id);

        if (!$jobOrder) {
            return response()->json(['message' => 'Job Order not found'], 404);
        }

        try {
            $history = RepairHistory::where('job_order_id', $id)
                ->orderBy('performed_at', 'desc')
                ->get();

            return response()->json($history, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching repair history: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }

    /**
     * Generate job order number
     */
    private function generateJobOrderNumber($customerType)
    {
        try {
            if ($customerType === 'technician-customer') {
                $latestTechJob = JobOrder::where('job_order_number', 'like', 'T%')
                    ->orderBy('id', 'desc')
                    ->first();
                $number = $latestTechJob ? ((int) substr($latestTechJob->job_order_number, 1)) + 1 : 1;
                return sprintf("T%05d", $number);
            } else {
                $latestRegularJob = JobOrder::whereRaw('job_order_number REGEXP "^[0-9]+$"')
                    ->orderBy('id', 'desc')
                    ->first();
                $number = $latestRegularJob ? ((int) $latestRegularJob->job_order_number) + 1 : 1;
                return str_pad($number, 5, '0', STR_PAD_LEFT);
            }
        } catch (\Exception $e) {
            // Fallback to timestamp-based generation if query fails
            $prefix = $customerType === 'technician-customer' ? 'T' : '';
            return $prefix . date('ymd') . rand(100, 999);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => JobOrder::count(),
                'pending' => JobOrder::where('status', 'Pending')->count(),
                'in_progress' => JobOrder::where('status', 'In Progress')->count(),
                'completed' => JobOrder::whereIn('status', ['Completed', 'Completed/claimed'])->count(),
                'unrepairable' => JobOrder::where('status', 'Unrepairable/pullout')->count(),
            ];

            // Add revenue if column exists
            if ($this->checkColumnExists('job_orders', 'repair_price')) {
                $stats['revenue_this_month'] = JobOrder::whereMonth('created_at', date('m'))
                    ->whereYear('created_at', date('Y'))
                    ->sum('repair_price');
            } else {
                $stats['revenue_this_month'] = 0;
            }

            return response()->json($stats, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching statistics: ' . $e->getMessage());
            return response()->json([
                'total' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'completed' => 0,
                'unrepairable' => 0,
                'revenue_this_month' => 0
            ], 200);
        }
    }

    /**
     * Check if a table exists
     */
    private function checkTableExists($table)
    {
        try {
            return \Schema::hasTable($table);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a column exists
     */
    private function checkColumnExists($table, $column)
    {
        try {
            return \Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Keep existing image methods
    public function uploadImages(Request $request, $id)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $jobOrder = JobOrder::findOrFail($id);
        $uploadedImages = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('job-order-images', 'public');
                $jobOrder->images()->create(['path' => $path]);
                $uploadedImages[] = Storage::url($path);
            }
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'images' => $uploadedImages
        ]);
    }

    public function getImages($id)
    {
        $jobOrder = JobOrder::with('images')->find($id);

        if (!$jobOrder) {
            return response()->json(['message' => 'Job order not found'], 404);
        }

        $imageUrls = $jobOrder->images->map(function ($image) {
            return Storage::url($image->path);
        });

        return response()->json($imageUrls, 200);
    }

    public function deleteImage(Request $request, $id)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $jobOrder = JobOrder::findOrFail($id);
        $image = $jobOrder->images()->where('path', $request->path)->first();

        if (!$image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully'], 200);
    }

    public function destroy($id)
    {
        $jobOrder = JobOrder::find($id);

        if (!$jobOrder) {
            return response()->json(['message' => 'Job Order not found'], 404);
        }

        $jobOrder->delete();
        
        return response()->json(['message' => 'Job Order deleted successfully'], 200);
    }

    public function create()
    {
        return view('job-orders.create');
    }
}