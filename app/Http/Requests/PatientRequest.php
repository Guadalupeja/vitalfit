<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'treatment_id' => ['nullable', 'integer', 'exists:treatments,id'],

            'sessions_purchased' => ['required', 'integer', 'min:0', 'max:999'],
            'package_total' => ['required', 'numeric', 'min:0', 'max:999999.99'],

            'active' => ['nullable', 'boolean'],
        ];
    }
}
