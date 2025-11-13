<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TicketDepartment;
use Illuminate\Http\Request;

class TicketDepartmentController extends Controller
{
    public function index()
    {
        $departments = TicketDepartment::withCount('tickets')->get();
        return view('admin.ticket-departments.index', compact('departments'));
    }

    public function create()
    {
        return view('admin.ticket-departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:ticket_departments,name',
            'description' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'auto_assign_users' => 'nullable|array',
            'auto_assign_users.*' => 'exists:users,id',
            'is_active' => 'boolean',
        ]);

        $department = TicketDepartment::create([
            'name' => $request->name,
            'description' => $request->description,
            'email' => $request->email,
            'auto_assign_users' => $request->auto_assign_users ? json_encode($request->auto_assign_users) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket_department.created',
            'description' => "Created ticket department: {$department->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.ticket-departments.index')
            ->with('success', 'Ticket department created successfully.');
    }

    public function show(TicketDepartment $ticketDepartment)
    {
        $ticketDepartment->load('tickets');
        return view('admin.ticket-departments.show', compact('ticketDepartment'));
    }

    public function edit(TicketDepartment $ticketDepartment)
    {
        $staffUsers = \App\Models\User::whereHas('role', function($q) {
            $q->whereIn('name', ['super_admin', 'billing_admin', 'support']);
        })->get();

        return view('admin.ticket-departments.edit', compact('ticketDepartment', 'staffUsers'));
    }

    public function update(Request $request, TicketDepartment $ticketDepartment)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:ticket_departments,name,' . $ticketDepartment->id,
            'description' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'auto_assign_users' => 'nullable|array',
            'auto_assign_users.*' => 'exists:users,id',
            'is_active' => 'boolean',
        ]);

        $ticketDepartment->update([
            'name' => $request->name,
            'description' => $request->description,
            'email' => $request->email,
            'auto_assign_users' => $request->auto_assign_users ? json_encode($request->auto_assign_users) : null,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket_department.updated',
            'description' => "Updated ticket department: {$ticketDepartment->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.ticket-departments.index')
            ->with('success', 'Ticket department updated successfully.');
    }

    public function destroy(TicketDepartment $ticketDepartment)
    {
        // Check if department has tickets
        if ($ticketDepartment->tickets()->exists()) {
            return back()->with('error', 'Cannot delete department with existing tickets. Please reassign or delete tickets first.');
        }

        $name = $ticketDepartment->name;
        $ticketDepartment->delete();

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket_department.deleted',
            'description' => "Deleted ticket department: {$name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.ticket-departments.index')
            ->with('success', 'Ticket department deleted successfully.');
    }

    public function toggleStatus(TicketDepartment $ticketDepartment)
    {
        $ticketDepartment->update(['is_active' => !$ticketDepartment->is_active]);

        $status = $ticketDepartment->is_active ? 'activated' : 'deactivated';

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "ticket_department.{$status}",
            'description' => "Ticket department {$status}: {$ticketDepartment->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Ticket department {$status} successfully.");
    }
}
