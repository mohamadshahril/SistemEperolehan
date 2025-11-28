<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeliveryOrderUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Use policy for real world authorization
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Get the DeliveryOrder ID from the route parameters (assuming the route uses {delivery_order})
        $deliveryOrderId = $this->route('delivery_order');

        return [
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            
            // Ensures do_number is unique, except for the current record being updated
            'do_number' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('delivery_orders', 'do_number')->ignore($deliveryOrderId),
            ],
            
            'delivery_date' => ['required', 'date'],
            
            // When updating, the file upload is optional. If present, it must be valid.
            'delivery_file' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:5120'], 
            
            'notes' => ['nullable', 'string'],
            
            // The is_received status should typically be handled by a specific 'confirm' action,
            // but is included here if it needs to be updated manually.
            'is_received' => ['boolean'], 
        ];
    }
}