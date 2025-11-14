<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can checkout
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'billing_cycle' => ['required', 'in:monthly,quarterly,semi_annually,annually,biennially,triennially,one_time'],
            'payment_gateway' => ['required', 'string'],
            'domain' => ['nullable', 'string', 'max:255'],
            'domain_type' => ['nullable', 'in:register,transfer,existing,subdomain'],
            'username' => ['nullable', 'string', 'max:255'],
            'config_options' => ['nullable', 'array'],
            'addons' => ['nullable', 'array'],
            'addons.*' => ['exists:addons,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // Billing information
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'agree_tos' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'billing_cycle' => 'billing cycle',
            'payment_gateway' => 'payment method',
            'domain_type' => 'domain option',
            'config_options' => 'configuration options',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'postal_code' => 'postal/ZIP code',
            'agree_tos' => 'terms of service',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'agree_tos.required' => 'You must agree to the Terms of Service to continue.',
            'agree_tos.accepted' => 'You must accept the Terms of Service.',
        ];
    }
}
