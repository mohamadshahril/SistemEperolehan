<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locationId = $this->route('location')?->id ?? $this->route('id');

        return [
            'location_iso_code' => [
                'required', 'string', 'max:10',
                Rule::unique('locations', 'location_iso_code')->ignore($locationId),
            ],
            'location_name' => [
                'required', 'string', 'max:100',
                Rule::unique('locations', 'location_name')->ignore($locationId),
            ],
            'parent_iso_code' => ['nullable', 'string', 'max:10'],
        ];
    }
}
