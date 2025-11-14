<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderUpdateRequest extends FormRequest
{
    public function rules(): array {
        return [
            'vendor_id' => ['sometimes', 'integer'],
            'status' => ['sometimes', 'string'],
        ];
    }
}
