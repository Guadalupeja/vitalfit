<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;


class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'treatment_id' => ['nullable', 'integer', 'exists:treatments,id'],
            'specialist_id' => ['required', 'integer', 'exists:users,id'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'status' => ['required', 'string', 'in:confirmed,cancelled,completed,no_show,pending'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'patient_treatment_id' => ['nullable', 'integer', 'exists:patient_treatments,id'],

        ];
    }
}
