<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketDepartment;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $client = Auth::user()->client;

        $query = Ticket::where('client_id', $client->id)
            ->with(['department', 'service.product']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(20);

        // Statistics
        $stats = [
            'total' => Ticket::where('client_id', $client->id)->count(),
            'open' => Ticket::where('client_id', $client->id)->where('status', 'open')->count(),
            'answered' => Ticket::where('client_id', $client->id)->where('status', 'answered')->count(),
            'on_hold' => Ticket::where('client_id', $client->id)->where('status', 'on_hold')->count(),
            'closed' => Ticket::where('client_id', $client->id)->where('status', 'closed')->count(),
        ];

        return view('client.tickets.index', compact('tickets', 'stats'));
    }

    public function create()
    {
        $client = Auth::user()->client;

        $departments = TicketDepartment::where('is_active', true)->get();
        $services = \App\Models\Service::where('client_id', $client->id)
            ->with('product')
            ->get();

        return view('client.tickets.create', compact('departments', 'services'));
    }

    public function store(Request $request)
    {
        $client = Auth::user()->client;

        $request->validate([
            'department_id' => 'required|exists:ticket_departments,id',
            'service_id' => 'nullable|exists:services,id',
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string|min:10|max:5000',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
        ]);

        // Verify service belongs to client if provided
        if ($request->service_id) {
            $service = \App\Models\Service::where('id', $request->service_id)
                ->where('client_id', $client->id)
                ->firstOrFail();
        }

        DB::beginTransaction();

        try {
            // Generate ticket number
            $lastTicket = Ticket::latest('id')->first();
            $ticketNumber = 'TKT-' . date('Ymd') . '-' . str_pad(($lastTicket?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);

            // Create ticket
            $ticket = Ticket::create([
                'client_id' => $client->id,
                'department_id' => $request->department_id,
                'service_id' => $request->service_id,
                'ticket_number' => $ticketNumber,
                'subject' => $request->subject,
                'priority' => $request->priority,
                'message' => $request->message,
                'status' => 'open',
                'last_reply_at' => now(),
                'last_reply_by' => 'client',
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
                $ticket->update(['attachments' => json_encode($attachments)]);
            }

            DB::commit();

            // TODO: Send notification email to department

            return redirect()->route('client.tickets.show', $ticket)
                ->with('success', 'Ticket created successfully. Our team will respond shortly.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create ticket: ' . $e->getMessage());
        }
    }

    public function show(Ticket $ticket)
    {
        // Authorization check
        if ($ticket->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        $ticket->load([
            'department',
            'service.product',
            'replies.user',
        ]);

        // Mark as read by client
        if ($ticket->last_reply_by === 'staff' && !$ticket->client_read_at) {
            $ticket->update(['client_read_at' => now()]);
        }

        return view('client.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        // Authorization check
        if ($ticket->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        // Check if ticket is closed
        if ($ticket->status === 'closed') {
            return back()->with('error', 'Cannot reply to a closed ticket. Please reopen it first.');
        }

        $request->validate([
            'message' => 'required|string|min:10|max:5000',
            'attachments.*' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,pdf,doc,docx,txt',
        ]);

        DB::beginTransaction();

        try {
            // Create reply
            $reply = TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'is_staff_reply' => false,
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
            $ticket->update([
                'last_reply_at' => now(),
                'last_reply_by' => 'client',
                'status' => 'open', // Reopen if was answered
                'admin_read_at' => null,
            ]);

            DB::commit();

            // TODO: Send notification email to assigned staff

            return back()->with('success', 'Reply added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to add reply: ' . $e->getMessage());
        }
    }

    public function close(Ticket $ticket)
    {
        // Authorization check
        if ($ticket->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        if ($ticket->status === 'closed') {
            return back()->with('info', 'Ticket is already closed.');
        }

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Ticket closed successfully.');
    }

    public function reopen(Ticket $ticket)
    {
        // Authorization check
        if ($ticket->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        if ($ticket->status !== 'closed') {
            return back()->with('error', 'Only closed tickets can be reopened.');
        }

        $ticket->update([
            'status' => 'open',
            'closed_at' => null,
            'closed_by' => null,
        ]);

        return back()->with('success', 'Ticket reopened successfully.');
    }

    public function rate(Request $request, Ticket $ticket)
    {
        // Authorization check
        if ($ticket->client_id !== Auth::user()->client->id) {
            abort(403, 'Unauthorized access to this ticket.');
        }

        // Can only rate closed tickets
        if ($ticket->status !== 'closed') {
            return back()->with('error', 'Only closed tickets can be rated.');
        }

        // Can only rate once
        if ($ticket->rating !== null) {
            return back()->with('error', 'This ticket has already been rated.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $ticket->update([
            'rating' => $request->rating,
            'rating_feedback' => $request->feedback,
        ]);

        return back()->with('success', 'Thank you for your feedback!');
    }
}
