<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ProductGroup;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    public function index()
    {
        $groups = ProductGroup::withCount('products')->get();
        return view('admin.product-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('admin.product-groups.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'boolean',
        ]);

        $group = ProductGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_visible' => $request->boolean('is_visible', true),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product_group.created',
            'description' => "Created product group: {$group->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.product-groups.index')
            ->with('success', 'Product group created successfully.');
    }

    public function show(ProductGroup $productGroup)
    {
        $productGroup->load('products');
        return view('admin.product-groups.show', compact('productGroup'));
    }

    public function edit(ProductGroup $productGroup)
    {
        return view('admin.product-groups.edit', compact('productGroup'));
    }

    public function update(Request $request, ProductGroup $productGroup)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_visible' => 'boolean',
        ]);

        $productGroup->update([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_visible' => $request->boolean('is_visible'),
        ]);

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product_group.updated',
            'description' => "Updated product group: {$productGroup->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.product-groups.index')
            ->with('success', 'Product group updated successfully.');
    }

    public function destroy(ProductGroup $productGroup)
    {
        // Check if group has products
        if ($productGroup->products()->exists()) {
            return back()->with('error', 'Cannot delete product group with existing products. Please reassign or delete products first.');
        }

        $name = $productGroup->name;
        $productGroup->delete();

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product_group.deleted',
            'description' => "Deleted product group: {$name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.product-groups.index')
            ->with('success', 'Product group deleted successfully.');
    }

    public function toggleVisibility(ProductGroup $productGroup)
    {
        $productGroup->update(['is_visible' => !$productGroup->is_visible]);

        $status = $productGroup->is_visible ? 'visible' : 'hidden';

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => "product_group.{$status}",
            'description' => "Product group set to {$status}: {$productGroup->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', "Product group set to {$status} successfully.");
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'groups' => 'required|array',
            'groups.*.id' => 'required|exists:product_groups,id',
            'groups.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($request->groups as $groupData) {
            ProductGroup::where('id', $groupData['id'])
                ->update(['sort_order' => $groupData['sort_order']]);
        }

        // Audit log
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'product_group.order_updated',
            'description' => 'Updated product group ordering',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Product group order updated successfully.');
    }
}
