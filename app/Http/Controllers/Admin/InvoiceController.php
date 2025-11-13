<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['client.user', 'service']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('client.user', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->latest()->paginate(20);
        $clients = Client::with('user')->get();

        return view('admin.invoices.index', compact('invoices', 'clients'));
    }

    public function create()
    {
        $clients = Client::with('user')->get();
        $services = Service::with(['client.user', 'product'])->get();

        return view('admin.invoices.create', compact('clients', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'nullable|exists:services,id',
            'due_date' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'send_email' => 'boolean',
        ]);

        DB::beginTransaction();

        try {
            // Calculate total
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['amount'] * $item['quantity'];
            }

            $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 0;
            $tax = $subtotal * ($taxRate / 100);
            $total = $subtotal + $tax;

            // Generate invoice number
            $lastInvoice = Invoice::latest('id')->first();
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(($lastInvoice?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);

            // Create invoice
            $invoice = Invoice::create([
                'client_id' => $request->client_id,
                'service_id' => $request->service_id,
                'invoice_number' => $invoiceNumber,
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'status' => 'unpaid',
                'notes' => $request->notes,
            ]);

            // Create invoice items
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['amount'],
                    'total' => $item['amount'] * $item['quantity'],
                ]);
            }

            DB::commit();

            // Send email if requested
            if ($request->boolean('send_email')) {
                $this->sendInvoiceEmail($invoice);
            }

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'invoice.created',
                'description' => "Created invoice: {$invoice->invoice_number} for client ID {$invoice->client_id}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Invoice created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['client.user', 'service.product', 'items', 'payments']);
        return view('admin.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        // Only allow editing unpaid invoices
        if ($invoice->status !== 'unpaid') {
            return back()->with('error', 'Only unpaid invoices can be edited.');
        }

        $invoice->load('items');
        $clients = Client::with('user')->get();
        $services = Service::with(['client.user', 'product'])->get();

        return view('admin.invoices.edit', compact('invoice', 'clients', 'services'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        // Only allow editing unpaid invoices
        if ($invoice->status !== 'unpaid') {
            return back()->with('error', 'Only unpaid invoices can be edited.');
        }

        $request->validate([
            'due_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Calculate new total
            $subtotal = 0;
            foreach ($request->items as $item) {
                $subtotal += $item['amount'] * $item['quantity'];
            }

            $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 0;
            $tax = $subtotal * ($taxRate / 100);
            $total = $subtotal + $tax;

            // Update invoice
            $invoice->update([
                'due_date' => $request->due_date,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'notes' => $request->notes,
            ]);

            // Delete old items and create new ones
            $invoice->items()->delete();
            foreach ($request->items as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['amount'],
                    'total' => $item['amount'] * $item['quantity'],
                ]);
            }

            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'invoice.updated',
                'description' => "Updated invoice: {$invoice->invoice_number}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->route('admin.invoices.show', $invoice)
                ->with('success', 'Invoice updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        // Prevent deletion if invoice has payments
        if ($invoice->payments()->exists()) {
            return back()->with('error', 'Cannot delete invoice with associated payments.');
        }

        // Only allow deletion of unpaid or cancelled invoices
        if (!in_array($invoice->status, ['unpaid', 'cancelled'])) {
            return back()->with('error', 'Only unpaid or cancelled invoices can be deleted.');
        }

        $invoiceNumber = $invoice->invoice_number;

        DB::beginTransaction();
        try {
            $invoice->items()->delete();
            $invoice->delete();
            DB::commit();

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'invoice.deleted',
                'description' => "Deleted invoice: {$invoiceNumber}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return redirect()->route('admin.invoices.index')
                ->with('success', 'Invoice deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete invoice: ' . $e->getMessage());
        }
    }

    public function markAsPaid(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice is already marked as paid.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'invoice.marked_paid',
            'description' => "Manually marked invoice as paid: {$invoice->invoice_number}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Invoice marked as paid successfully.');
    }

    public function cancel(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot cancel a paid invoice.');
        }

        $invoice->update(['status' => 'cancelled']);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'invoice.cancelled',
            'description' => "Cancelled invoice: {$invoice->invoice_number}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Invoice cancelled successfully.');
    }

    public function sendEmail(Invoice $invoice)
    {
        try {
            $this->sendInvoiceEmail($invoice);

            // Audit log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'invoice.email_sent',
                'description' => "Sent invoice email: {$invoice->invoice_number}",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return back()->with('success', 'Invoice email sent successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send invoice email: ' . $e->getMessage());
        }
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['client.user', 'service.product', 'items']);

        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice'));

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'invoice.pdf_downloaded',
            'description' => "Downloaded PDF for invoice: {$invoice->invoice_number}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    protected function sendInvoiceEmail(Invoice $invoice)
    {
        $invoice->load(['client.user', 'items']);

        $client = $invoice->client;
        $email = $client->user->email;

        Mail::send('emails.invoice', compact('invoice', 'client'), function($message) use ($email, $invoice) {
            $message->to($email)
                    ->subject("Invoice {$invoice->invoice_number} - " . config('app.name'));
        });
    }
}
