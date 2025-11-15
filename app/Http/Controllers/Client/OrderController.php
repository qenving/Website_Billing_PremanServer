<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        // Get visible product groups with active products
        $productGroups = ProductGroup::where('is_visible', true)
            ->orderBy('sort_order')
            ->with(['products' => function($q) {
                $q->where('is_active', true)
                  ->where(function($q2) {
                      $q2->whereNull('stock')
                         ->orWhere('stock', '>', 0);
                  });
            }])
            ->get();

        return view('client.orders.index', compact('productGroups'));
    }

    public function configure(Product $product)
    {
        // Check if product is available
        if (!$product->is_active) {
            return redirect()->route('client.orders.index')
                ->with('error', 'This product is not available.');
        }

        if ($product->stock !== null && $product->stock <= 0) {
            return redirect()->route('client.orders.index')
                ->with('error', 'This product is out of stock.');
        }

        $product->load('group');

        // Get provisioning config schema if available
        $configFields = [];
        if ($product->provisioning_config) {
            $configFields = json_decode($product->provisioning_config, true) ?? [];
        }

        return view('client.orders.configure', compact('product', 'configFields'));
    }

    public function addToCart(Request $request, Product $product)
    {
        // Check if product is available
        if (!$product->is_active) {
            return back()->with('error', 'This product is not available.');
        }

        if ($product->stock !== null && $product->stock <= 0) {
            return back()->with('error', 'This product is out of stock.');
        }

        $request->validate([
            'billing_cycle' => 'required|string',
            'domain' => 'nullable|string|max:255',
            'config' => 'nullable|array',
        ]);

        // Store in session cart
        $cart = session()->get('cart', []);

        $cartItem = [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'billing_cycle' => $request->billing_cycle,
            'price' => $product->price,
            'domain' => $request->domain,
            'config' => $request->config ?? [],
            'added_at' => now()->toDateTimeString(),
        ];

        $cart[] = $cartItem;
        session()->put('cart', $cart);

        return redirect()->route('client.orders.cart')
            ->with('success', 'Product added to cart successfully.');
    }

    public function cart()
    {
        $cart = session()->get('cart', []);

        // Calculate totals
        $subtotal = collect($cart)->sum('price');
        $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 0;
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax;

        // Check client credit balance
        $client = Auth::user()->client;
        $creditAvailable = $client->credit_balance;

        return view('client.orders.cart', compact('cart', 'subtotal', 'tax', 'total', 'creditAvailable'));
    }

    public function removeFromCart(Request $request, $index)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$index])) {
            unset($cart[$index]);
            $cart = array_values($cart); // Reindex array
            session()->put('cart', $cart);

            return back()->with('success', 'Item removed from cart.');
        }

        return back()->with('error', 'Item not found in cart.');
    }

    public function clearCart()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared successfully.');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('client.orders.index')
                ->with('error', 'Your cart is empty.');
        }

        // Calculate totals
        $subtotal = collect($cart)->sum('price');
        $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 0;
        $tax = $subtotal * ($taxRate / 100);
        $total = $subtotal + $tax;

        // Get available payment gateways
        $paymentGateways = \App\Models\Extension::where('type', 'payment_gateway')
            ->where('enabled', true)
            ->get();

        $client = Auth::user()->client;

        return view('client.orders.checkout', compact('cart', 'subtotal', 'tax', 'total', 'paymentGateways', 'client'));
    }

    public function processCheckout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('client.orders.index')
                ->with('error', 'Your cart is empty.');
        }

        $request->validate([
            'payment_method' => 'required|string',
            'use_credit' => 'boolean',
        ]);

        $client = Auth::user()->client;

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = collect($cart)->sum('price');
            $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 0;
            $tax = $subtotal * ($taxRate / 100);
            $total = $subtotal + $tax;

            // Apply credit if requested
            $creditUsed = 0;
            if ($request->boolean('use_credit') && $client->credit_balance > 0) {
                $creditUsed = min($client->credit_balance, $total);
                $total -= $creditUsed;

                // Deduct credit
                $client->decrement('credit_balance', $creditUsed);
            }

            // Generate invoice number
            $lastInvoice = Invoice::latest('id')->first();
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(($lastInvoice?->id ?? 0) + 1, 5, '0', STR_PAD_LEFT);

            // Create invoice
            $invoice = Invoice::create([
                'client_id' => $client->id,
                'invoice_number' => $invoiceNumber,
                'due_date' => now()->addDays(3),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'status' => $total <= 0 ? 'paid' : 'unpaid',
                'paid_at' => $total <= 0 ? now() : null,
            ]);

            // Create services and invoice items
            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);

                // Create service
                $service = Service::create([
                    'client_id' => $client->id,
                    'product_id' => $product->id,
                    'billing_cycle' => $item['billing_cycle'],
                    'price' => $item['price'],
                    'domain' => $item['domain'] ?? null,
                    'username' => null,
                    'password' => null,
                    'status' => 'pending',
                    'next_due_date' => $this->calculateNextDueDate($item['billing_cycle']),
                    'provisioning_config' => json_encode($item['config']),
                ]);

                // Create invoice item
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "{$product->name} - {$item['billing_cycle']}",
                    'quantity' => 1,
                    'unit_price' => $item['price'],
                    'total' => $item['price'],
                ]);

                // Update invoice with first service
                if (!$invoice->service_id) {
                    $invoice->update(['service_id' => $service->id]);
                }

                // Decrement stock if applicable
                if ($product->stock !== null) {
                    $product->decrement('stock');
                }
            }

            // Record credit usage
            if ($creditUsed > 0) {
                DB::table('credit_transactions')->insert([
                    'client_id' => $client->id,
                    'amount' => $creditUsed,
                    'type' => 'debit',
                    'description' => "Applied to invoice {$invoiceNumber}",
                    'balance_before' => $client->credit_balance + $creditUsed,
                    'balance_after' => $client->credit_balance,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            // Clear cart
            session()->forget('cart');

            // If paid with credit, activate services
            if ($total <= 0) {
                return redirect()->route('client.services.index')
                    ->with('success', 'Order completed successfully using account credit!');
            }

            // Redirect to payment
            return redirect()->route('client.invoices.pay', $invoice)
                ->with('success', 'Order created successfully. Please complete payment.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process order: ' . $e->getMessage());
        }
    }

    protected function calculateNextDueDate(string $billingCycle): \DateTime
    {
        $date = now();

        return match($billingCycle) {
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addMonths(3),
            'semi_annually' => $date->addMonths(6),
            'annually' => $date->addYear(),
            'biennially' => $date->addYears(2),
            'triennially' => $date->addYears(3),
            default => $date->addMonth(),
        };
    }
}
