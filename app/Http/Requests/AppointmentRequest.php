<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],

            'patient_package_id' => ['nullable', 'integer', 'exists:patient_packages,id'],
            'patient_package_item_id' => ['nullable', 'integer', 'exists:patient_package_items,id'],

            'treatment_id' => ['nullable', 'integer', 'exists:treatments,id'],
            'specialist_id' => ['required', 'integer', 'exists:users,id'],

            'status' => ['required', 'in:confirmed,pending,cancelled,completed,no_show'],
            'notes' => ['nullable', 'string', 'max:2000'],

            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Debes seleccionar un paciente.',
            'patient_id.exists' => 'El paciente seleccionado no existe.',

            'patient_package_id.exists' => 'El paquete seleccionado no existe.',
            'patient_package_item_id.exists' => 'El tratamiento del paquete seleccionado no existe.',

            'treatment_id.exists' => 'El tratamiento seleccionado no existe.',
            'specialist_id.required' => 'Debes seleccionar un especialista.',
            'specialist_id.exists' => 'El especialista seleccionado no existe.',

            'status.required' => 'Debes seleccionar un estatus.',
            'status.in' => 'El estatus seleccionado no es válido.',

            'notes.max' => 'Las notas no deben exceder 2000 caracteres.',

            'start_at.required' => 'Debes indicar la fecha y hora de inicio.',
            'start_at.date' => 'La fecha y hora de inicio no es válida.',

            'end_at.required' => 'Debes indicar la fecha y hora de fin.',
            'end_at.date' => 'La fecha y hora de fin no es válida.',
            'end_at.after' => 'La fecha y hora de fin debe ser posterior a la de inicio.',
        ];
    }
}