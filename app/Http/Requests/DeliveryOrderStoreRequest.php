<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryOrderStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Use policy for real world authorization
    }

    public function rules(): array
    {
        return [
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'do_number' => ['required', 'string', 'max:255', 'unique:delivery_orders,do_number'],
            'delivery_date' => ['required', 'date'],
            // Rule for the uploaded file: max 5MB, PDF, JPG, or PNG
            'delivery_file' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ];
    }
}