<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['required', 'exists:products,id'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,semi_annually,annually,biennially,triennially,one_time'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,active,suspended,terminated,cancelled'],
            'domain' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string'],
            'next_due_date' => ['nullable', 'date'],
            'config_options' => ['nullable', 'array'],
            'server_details' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'client_id' => 'client',
            'product_id' => 'product',
            'billing_cycle' => 'billing cycle',
            'next_due_date' => 'next due date',
            'config_options' => 'configuration options',
            'server_details' => 'server details',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If password is provided, it will be encrypted by the controller
        // Convert server_details to array if it's JSON string
        if ($this->has('server_details') && is_string($this->server_details)) {
            $this->merge([
                'server_details' => json_decode($this->server_details, true),
            ]);
        }
    }
}
