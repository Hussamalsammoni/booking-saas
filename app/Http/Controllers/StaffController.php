<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        return Inertia::render('Staff/Index', [
            'staff' => Staff::with('user')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Staff/Create', [
            'services' => \App\Models\Service::where('is_active', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'title' => 'nullable|string|max:255',
            'can_view_all_bookings' => 'boolean',
            'working_hours' => 'nullable|array',
            'service_ids' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'staff',
        ]);

        $staff = Staff::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'can_view_all_bookings' => $request->boolean('can_view_all_bookings'),
            'working_hours' => $request->working_hours,
        ]);

        $staff->services()->sync($request->service_ids ?? []);

        return redirect()->route('staff.index')->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function edit(Staff $staff)
    {
        return Inertia::render('Staff/Edit', [
            'staff' => $staff->load('user', 'services'),
            'services' => \App\Models\Service::where('is_active', true)->get(),
        ]);
    }

    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'can_view_all_bookings' => 'boolean',
            'working_hours' => 'nullable|array',
            'service_ids' => 'nullable|array',
        ]);

        $staff->user->update(['name' => $request->name]);
        $staff->update([
            'title' => $request->title,
            'is_active' => $request->is_active,
            'can_view_all_bookings' => $request->boolean('can_view_all_bookings'),
            'working_hours' => $request->working_hours,
        ]);

        $staff->services()->sync($request->service_ids ?? []);

        return redirect()->route('staff.index')->with('success', 'تم تحديث بيانات الموظف بنجاح');
    }

    public function destroy(Staff $staff)
    {
        $staff->user->delete();
        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'تم حذف الموظف بنجاح');
    }
}