<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(['super_admin', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_group_id' => ['required', 'exists:product_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,semi_annually,annually,biennially,triennially,one_time'],
            'setup_fee' => ['nullable', 'numeric', 'min:0'],
            'module' => ['nullable', 'string', 'max:50'],
            'module_config' => ['nullable', 'array'],
            'module_package' => ['nullable', 'string', 'max:255'],
            'auto_provision' => ['sometimes', 'boolean'],
            'stock_enabled' => ['sometimes', 'boolean'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'config_options' => ['nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_group_id' => 'product group',
            'billing_cycle' => 'billing cycle',
            'setup_fee' => 'setup fee',
            'auto_provision' => 'auto-provision',
            'stock_enabled' => 'stock control',
            'stock_quantity' => 'stock quantity',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $this->merge([
            'auto_provision' => $this->has('auto_provision'),
            'stock_enabled' => $this->has('stock_enabled'),
            'is_active' => $this->has('is_active'),
            'is_featured' => $this->has('is_featured'),
        ]);
    }
}
