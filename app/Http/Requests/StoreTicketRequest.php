<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // All authenticated users can create tickets
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'min:5', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'service_id' => ['nullable', 'exists:services,id'],
            'message' => ['required', 'string', 'min:20'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,txt,zip'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'department_id' => 'department',
            'service_id' => 'related service',
            'attachments.*' => 'attachment',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subject.min' => 'Subject must be at least 5 characters.',
            'message.min' => 'Message must be at least 20 characters for better support.',
            'attachments.max' => 'You can upload maximum 5 files.',
            'attachments.*.max' => 'Each file must not exceed 5MB.',
            'attachments.*.mimes' => 'Only images, PDF, documents, and ZIP files are allowed.',
        ];
    }
}
