<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('group');

        // Filter by group
        if ($request->filled('group')) {
            $query->where('group_id', $request->group);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(20);
        $groups = ProductGroup::all();

        return view('admin.products.index', compact('products', 'groups'));
    }

    public function create()
    {
        $groups = ProductGroup::all();
        $types = ['vps', 'dedicated', 'game_server', 'web_hosting', 'other'];
        $billingCycles = ['monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially'];

        return view('admin.products.create', compact('groups', 'types', 'billingCycles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:product_groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:vps,dedicated,game_server,web_hosting,other',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually,biennially,triennially',
            'currency' => 'required|string|size:3',
            'stock' => 'nullable|integer|min:0',
            'provisioning_extension' => 'nullable|string|max:50',
            'provisioning_config' => 'nullable|json',
            'allowed_payment_extensions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $product = Product::create([
            'group_id' => $request->group_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'price' => $request->price,
            'billing_cycle' => $request->billing_cycle,
            'currency' => $request->currency,
            'stock' => $request->stock,
            'provisioning_extension' => $request->provisioning_extension,
            'provisioning_config' => $request->provisioning_config,
            'allowed_payment_extensions' => $request->allowed_payment_extensions,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product.created',
            'description' => "Created product: {$product->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load('group');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $groups = ProductGroup::all();
        $types = ['vps', 'dedicated', 'game_server', 'web_hosting', 'other'];
        $billingCycles = ['monthly', 'quarterly', 'semi_annually', 'annually', 'biennially', 'triennially'];

        return view('admin.products.edit', compact('product', 'groups', 'types', 'billingCycles'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'group_id' => 'required|exists:product_groups,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:vps,dedicated,game_server,web_hosting,other',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually,biennially,triennially',
            'currency' => 'required|string|size:3',
            'stock' => 'nullable|integer|min:0',
            'provisioning_extension' => 'nullable|string|max:50',
            'provisioning_config' => 'nullable|json',
            'allowed_payment_extensions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $product->update([
            'group_id' => $request->group_id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'price' => $request->price,
            'billing_cycle' => $request->billing_cycle,
            'currency' => $request->currency,
            'stock' => $request->stock,
            'provisioning_extension' => $request->provisioning_extension,
            'provisioning_config' => $request->provisioning_config,
            'allowed_payment_extensions' => $request->allowed_payment_extensions,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product.updated',
            'description' => "Updated product: {$product->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // Check if product has active services
        if ($product->services()->where('status', '!=', 'terminated')->exists()) {
            return back()->with('error', 'Cannot delete product with active services.');
        }

        $name = $product->name;
        $product->delete();

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product.deleted',
            'description' => "Deleted product: {$name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        $status = $product->is_active ? 'activated' : 'deactivated';

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "product.{$status}",
            'description' => "Product {$status}: {$product->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Product {$status} successfully.");
    }
}
