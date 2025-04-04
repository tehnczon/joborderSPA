<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobOrderController extends Controller
{
    public function index()
    {
        return response()->json(JobOrder::all(), 200);
    }

    public function create()
    {
        return view('job-orders.create');
    }

    public function store(Request $request)
    {
        \Log::info('Job Order Request Data:', $request->all());

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
        ]);

        if (in_array($validatedData['status'] ?? '', ['Unrepairable/pullout', 'Completed/claimed'])) {
            $validatedData['pullout_date'] = now()->toDateString();
        }

        if ($validatedData['customer_type'] === 'technician-customer') {
            $latestTechJob = JobOrder::where('job_order_number', 'like', 'T%')->latest()->first();
            $number = $latestTechJob ? ((int) substr($latestTechJob->job_order_number, 1)) + 1 : 1;
            $jobOrderNumber = sprintf("T%05d", $number);
        } else {
            $latestRegularJob = JobOrder::whereRaw('job_order_number REGEXP "^[0-9]+$"')->latest()->first();
            $number = $latestRegularJob ? ((int) $latestRegularJob->job_order_number) + 1 : 1;
            $jobOrderNumber = str_pad($number, 5, '0', STR_PAD_LEFT);
        }

        $jobOrder = JobOrder::create([
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
        ]);

        return response()->json([
            'message' => 'Job Order Created!',
            'data' => $jobOrder
        ], 201);
    }

    public function show($id)
    {
        $jobOrder = JobOrder::find($id);

        if (!$jobOrder) {
            return response()->json(['message' => 'Job Order not found'], 404);
        }

        return response()->json($jobOrder, 200);
    }

    public function update(Request $request, $id)
    {
        $jobOrder = JobOrder::find($id);

        if (!$jobOrder) {
            return response()->json(['message' => 'Job Order not found'], 404);
        }

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
        ]);

        if (isset($validated['ram'])) {
            $validated['ram'] = json_encode($validated['ram']);
        }

        if (isset($validated['ssd'])) {
            $validated['ssd'] = json_encode($validated['ssd']);
        }

        if (isset($validated['status']) && in_array($validated['status'], ['Unrepairable/pullout', 'Completed/claimed'])) {
            $validated['pullout_date'] = now()->toDateString();
        }

        $jobOrder->update($validated);

        return response()->json([
            'message' => 'Job Order updated successfully',
            'job_order' => $jobOrder,
        ], 200);
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
}
