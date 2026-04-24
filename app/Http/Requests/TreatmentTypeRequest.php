<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'color_hex' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Debes capturar el nombre del tipo de tratamiento.',
            'color_hex.required' => 'Debes seleccionar un color.',
            'color_hex.regex' => 'El color debe tener formato hexadecimal válido, por ejemplo: #EC4899.',
        ];
    }
}