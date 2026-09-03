<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecialistScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')],
            'weekday' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'service_type' => ['required', Rule::in(['nutrition'])],
            'active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'Debes seleccionar una sucursal.',
            'weekday.required' => 'Debes seleccionar un día.',
            'start_time.required' => 'Debes indicar la hora de inicio.',
            'end_time.required' => 'Debes indicar la hora de fin.',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        ];
    }
}