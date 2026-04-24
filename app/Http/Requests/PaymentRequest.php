<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'patient_package_id' => ['required', 'integer', 'exists:patient_packages,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', 'in:cash,card,transfer,other'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Debes seleccionar un paciente.',
            'patient_package_id.required' => 'Debes seleccionar un paquete del paciente.',
            'amount.required' => 'Debes capturar el monto.',
            'amount.gt' => 'El monto debe ser mayor a 0.',
            'method.required' => 'Debes seleccionar un método de pago.',
            'paid_at.required' => 'Debes indicar la fecha del pago.',
        ];
    }
}