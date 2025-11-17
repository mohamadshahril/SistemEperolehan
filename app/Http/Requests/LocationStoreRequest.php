<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocationStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_iso_code' => ['required', 'string', 'max:10', 'unique:locations,location_iso_code'],
            'location_name' => ['required', 'string', 'max:100', 'unique:locations,location_name'],
            'parent_iso_code' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'integer', 'in:1,2'],
        ];
    }
}
