<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TreatmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'treatment_type_id' => ['required', 'integer', 'exists:treatment_types,id'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:360'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Debes capturar el nombre del tratamiento.',
            'treatment_type_id.required' => 'Debes seleccionar el tipo de tratamiento.',
            'treatment_type_id.exists' => 'El tipo de tratamiento seleccionado no existe.',
            'duration_minutes.required' => 'Debes capturar la duración.',
            'duration_minutes.integer' => 'La duración debe ser un número entero.',
            'duration_minutes.min' => 'La duración mínima es de 10 minutos.',
            'duration_minutes.max' => 'La duración máxima es de 360 minutos.',
        ];
    }
}