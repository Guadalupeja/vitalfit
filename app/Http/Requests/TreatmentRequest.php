<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // luego lo amarramos a roles si quieres
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:faciales,aparatologia,esteticos,laser,nutricion,valoracion'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:360'],
            'color_hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'La categoría no es válida.',
            'color_hex.regex' => 'El color debe estar en formato HEX, por ejemplo: #AABBCC.',
        ];
    }
}
