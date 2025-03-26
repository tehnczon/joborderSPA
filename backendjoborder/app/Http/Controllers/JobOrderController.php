<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use Illuminate\Http\Request;

class JobOrderController extends Controller
{
    public function index()
    {
        return response()->json(JobOrder::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_type' => 'required|in:customer,technician-customer',
            'customer_name' => 'required|string|max:255',
            'laptop_model' => 'required|string|max:255',
            'status' => 'nullable|in:Pending,In Progress,Completed',
            'pullout_date' => 'nullable|date',
            'ram' => 'nullable|string',
            'ssd' => 'nullable|string',
            'hdd' => 'nullable|string',
            'has_battery' => 'boolean',
            'has_wifi_card' => 'boolean',
            'others' => 'nullable|string',
            'without' => 'nullable|string',
        ]);

        $jobOrder = JobOrder::create($validated);
        return response()->json($jobOrder, 201);
    }

    public function show($id)
    {
        return response()->json(JobOrder::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $jobOrder = JobOrder::findOrFail($id);
        $jobOrder->update($request->all());
        return response()->json($jobOrder);
    }

    public function destroy($id)
    {
        JobOrder::destroy($id);
        return response()->json(['message' => 'Job Order deleted']);
    }
}
