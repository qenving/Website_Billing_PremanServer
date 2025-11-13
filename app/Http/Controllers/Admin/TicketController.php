<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Ticket;
use App\Models\TicketDepartment;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['client.user', 'department', 'assignedTo']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(20);
        $departments = TicketDepartment::all();
        $staffUsers = \App\Models\User::whereHas('role', function($q) {
            $q->whereIn('name', ['super_admin', 'billing_admin', 'support']);
        })->get();

        return view('admin.tickets.index', compact('tickets', 'departments', 'staffUsers'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'client.user',
            'department',
            'assignedTo',
            'replies.user',
            'service.product'
        ]);

        // Mark as read by admin
        if ($ticket->last_reply_by === 'client' && !$ticket->admin_read_at) {
            $ticket->update(['admin_read_at' => now()]);
        }

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string|min:10|max:5000',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
            'status' => 'nullable|in:open,on_hold,answered,closed',
        ]);

        DB::beginTransaction();

        try {
            // Create reply
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'is_staff_reply' => true,
            ]);

            // Handle attachments
            if ($request->hasFile('attachments')) {
                $attachments = [];
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('ticket-attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                    ];
                }
                $reply->update(['attachments' => json_encode($attachments)]);
            }

            // Update ticket
            $updateData = [
                'last_reply_at' => now(),
                'last_reply_by' => 'staff',
                'client_read_at' => null,
            ];

            // Update status if provided
            if ($request->filled('status')) {
                $updateData['status'] = $request->status;
            } else {
                // Auto-update to answered if currently open
                if ($ticket->status === 'open') {
                    $updateData['status'] = 'answered';
                }
            }

            $ticket->update($updateData);

            DB::commit();

            // Send email notification to client
            $this->sendReplyEmail($ticket, $reply);

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'ticket.replied',
                'description' => "Replied to ticket: {$ticket->ticket_number}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return back()->with('success', 'Reply added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to add reply: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,on_hold,answered,closed',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $request->status]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket.status_updated',
            'description' => "Updated ticket {$ticket->ticket_number} status from {$oldStatus} to {$request->status}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Ticket status updated successfully.');
    }

    public function updatePriority(Request $request, Ticket $ticket)
    {
        $request->validate([
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $oldPriority = $ticket->priority;
        $ticket->update(['priority' => $request->priority]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket.priority_updated',
            'description' => "Updated ticket {$ticket->ticket_number} priority from {$oldPriority} to {$request->priority}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Ticket priority updated successfully.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldAssignee = $ticket->assigned_to;
        $ticket->update(['assigned_to' => $request->assigned_to]);

        // Send notification to assigned user
        if ($request->assigned_to && $request->assigned_to !== $oldAssignee) {
            $assignedUser = \App\Models\User::find($request->assigned_to);
            // TODO: Send notification email to assigned user
        }

        // Audit log
        $description = $request->assigned_to
            ? "Assigned ticket {$ticket->ticket_number} to user ID {$request->assigned_to}"
            : "Unassigned ticket {$ticket->ticket_number}";

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket.assigned',
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Ticket assignment updated successfully.');
    }

    public function merge(Request $request, Ticket $ticket)
    {
        $request->validate([
            'target_ticket_id' => 'required|exists:tickets,id|different:' . $ticket->id,
        ]);

        $targetTicket = Ticket::findOrFail($request->target_ticket_id);

        // Verify both tickets belong to same client
        if ($ticket->client_id !== $targetTicket->client_id) {
            return back()->with('error', 'Can only merge tickets from the same client.');
        }

        DB::beginTransaction();

        try {
            // Move all replies to target ticket
            $ticket->replies()->update(['ticket_id' => $targetTicket->id]);

            // Add merge note to target ticket
            TicketReply::create([
                'ticket_id' => $targetTicket->id,
                'user_id' => auth()->id(),
                'message' => "Merged ticket #{$ticket->ticket_number} into this ticket by admin.",
                'is_staff_reply' => true,
                'is_internal' => true,
            ]);

            // Close the source ticket
            $ticket->update([
                'status' => 'closed',
                'merged_into' => $targetTicket->id,
            ]);

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'ticket.merged',
                'description' => "Merged ticket {$ticket->ticket_number} into {$targetTicket->ticket_number}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.tickets.show', $targetTicket)
                ->with('success', 'Tickets merged successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to merge tickets: ' . $e->getMessage());
        }
    }

    public function close(Ticket $ticket)
    {
        if ($ticket->status === 'closed') {
            return back()->with('error', 'Ticket is already closed.');
        }

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => auth()->id(),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket.closed',
            'description' => "Closed ticket: {$ticket->ticket_number}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket closed successfully.');
    }

    public function reopen(Ticket $ticket)
    {
        if ($ticket->status !== 'closed') {
            return back()->with('error', 'Only closed tickets can be reopened.');
        }

        $ticket->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'ticket.reopened',
            'description' => "Reopened ticket: {$ticket->ticket_number}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Ticket reopened successfully.');
    }

    public function delete(Ticket $ticket)
    {
        // Only allow deletion of closed tickets
        if ($ticket->status !== 'closed') {
            return back()->with('error', 'Only closed tickets can be deleted.');
        }

        $ticketNumber = $ticket->ticket_number;

        DB::beginTransaction();
        try {
            // Delete replies
            $ticket->replies()->delete();

            // Delete ticket
            $ticket->delete();

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'ticket.deleted',
                'description' => "Deleted ticket: {$ticketNumber}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.tickets.index')
                ->with('success', 'Ticket deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete ticket: ' . $e->getMessage());
        }
    }

    protected function sendReplyEmail(Ticket $ticket, TicketReply $reply)
    {
        try {
            $client = $ticket->client;
            $email = $client->user->email;

            Mail::send('emails.ticket-reply', compact('ticket', 'reply', 'client'), function($message) use ($email, $ticket) {
                $message->to($email)
                        ->subject("Re: [{$ticket->ticket_number}] {$ticket->subject}");
            });
        } catch (\Exception $e) {
            // Log email error but don't fail the reply
            \Log::error('Failed to send ticket reply email: ' . $e->getMessage());
        }
    }
}
