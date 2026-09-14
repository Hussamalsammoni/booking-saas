<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffApiController extends Controller
{
    public function index()
    {
        return response()->json(
            Staff::with(['user', 'services'])->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'nullable|string|max:255',
            'working_hours' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
            'service_ids' => 'sometimes|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $staff = Staff::create([
            'user_id' => $validated['user_id'],
            'title' => $validated['title'] ?? null,
            'working_hours' => $validated['working_hours'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (!empty($validated['service_ids'])) {
            $staff->services()->sync($validated['service_ids']);
        }

        return response()->json($staff->load(['user', 'services']), 201);
    }

    public function show(Staff $staff)
    {
        return response()->json($staff->load(['user', 'services']));
    }

    public function update(Request $request, Staff $staff)
    {
        $validated = $request->validate([
            'title' => 'sometimes|nullable|string|max:255',
            'working_hours' => 'sometimes|nullable|array',
            'is_active' => 'sometimes|boolean',
            'service_ids' => 'sometimes|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $staff->update($validated);

        if (isset($validated['service_ids'])) {
            $staff->services()->sync($validated['service_ids']);
        }

        return response()->json($staff->load(['user', 'services']));
    }

    public function destroy(Staff $staff)
    {
        $staff->delete();

        return response()->json(['message' => 'تم حذف الموظف بنجاح']);
    }
}