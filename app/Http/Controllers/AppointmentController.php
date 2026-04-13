<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    // Vista
public function index()
{
    $patients = Patient::query()
        ->where('active', true)
        ->orderBy('full_name')
        ->get(['id','full_name']);

    $treatments = Treatment::query()
        ->where('active', true)
        ->orderBy('category')
        ->orderBy('name')
        ->get(['id','name','category','duration_minutes','color_hex']);

    $specialists = User::query()
        ->orderBy('name')
        ->get(['id','name','role']);

        $currentUserId = Auth::id();
    return view('agenda.index', compact('patients', 'treatments', 'specialists', 'currentUserId'));
}

    // Fuente de eventos para FullCalendar (JSON)
    public function events(Request $request)
    {
        $start = $request->query('start'); // ISO
        $end   = $request->query('end');   // ISO

        $appointments = Appointment::query()
            ->with(['patient:id,full_name', 'treatment:id,name,color_hex', 'specialist:id,name', 'creator:id,name'])
            ->when($start && $end, fn($q) => $q->whereBetween('start_at', [$start, $end]))
            ->get();

        $events = $appointments->map(function ($a) {
            $titleParts = [];

            if ($a->patient) $titleParts[] = $a->patient->full_name;
            if ($a->treatment) $titleParts[] = $a->treatment->name;

            $title = implode(' — ', $titleParts) ?: 'Cita';

            $color = $a->treatment?->color_hex ?? '#111827'; // fallback gris

            // si cancelada/no_show: rojo
            if (in_array($a->status, ['cancelled','no_show'], true)) {
                $color = '#DC2626';
            }

            return [
                'id' => (string)$a->id,
                'title' => $title,
                'start' => $a->start_at->toIso8601String(),
                'end' => $a->end_at->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,

                // datos extra para el modal
                'extendedProps' => [
                    'patient_id' => $a->patient_id,
                    'treatment_id' => $a->treatment_id,
                    'specialist_id' => $a->specialist_id,
                    'status' => $a->status,
                    'notes' => $a->notes,
                    'creator_name' => $a->creator?->name,
                    'specialist_name' => $a->specialist?->name,
                    'patient_treatment_id' => $a->patient_treatment_id,
                ],
            ];
        });

        return response()->json($events);
    }

    // Crear cita (AJAX)
    public function store(AppointmentRequest $request)
{
    $data = $request->validated();

    // Forzar especialista y creador
    $data['created_by'] = Auth::id();

    // Validar paquete pertenece al paciente (lo tuyo está bien)
    if (!empty($data['patient_treatment_id'])) {
        $pkg = \App\Models\PatientTreatment::find($data['patient_treatment_id']);
        if (!$pkg || (int)$pkg->patient_id !== (int)($data['patient_id'] ?? 0)) {
            return response()->json(['message' => 'El paquete no pertenece al paciente seleccionado.'], 422);
        }
        $data['treatment_id'] = $pkg->treatment_id;
    }

    $start = Carbon::parse($data['start_at']);
    if ($start->isPast()) {
        return response()->json(['message' => 'No se puede crear una cita en una fecha pasada.'], 422);
    }

    if ($this->hasOverlap(null, $data['specialist_id'], $data['start_at'], $data['end_at'])) {
        return response()->json(['message' => 'No se puede agendar: ya existe una cita traslapada para ese especialista.'], 422);
    }

    $appointment = Appointment::create($data);

    return response()->json(['message' => 'Cita creada correctamente.', 'id' => $appointment->id]);
}


    // Actualizar cita (AJAX)
public function update(AppointmentRequest $request, Appointment $appointment)
{
    $data = $request->validated();

    if (!empty($data['patient_treatment_id'])) {
    $pkg = \App\Models\PatientTreatment::find($data['patient_treatment_id']);
    if (!$pkg || (int)$pkg->patient_id !== (int)($data['patient_id'] ?? 0)) {
        return response()->json(['message' => 'El paquete no pertenece al paciente seleccionado.'], 422);
    }
    // opcional: forzar treatment_id = del paquete
    $data['treatment_id'] = $pkg->treatment_id;
}


    $newStart = Carbon::parse($data['start_at']);
    $oldStart = $appointment->start_at;

    // 1) Si la cita ya es pasada, NO permitimos cambiarle fechas
    if ($oldStart->isPast()) {
        if (!$newStart->equalTo($oldStart)) {
            return response()->json([
                'message' => 'Esta cita ya ocurrió. Puedes actualizar estatus/notas, pero no cambiar la fecha/hora.'
            ], 422);
        }
    } else {
        // 2) Si era futura, no permitir moverla a pasado
        if ($newStart->isPast()) {
            return response()->json([
                'message' => 'No se puede mover una cita a una fecha pasada.'
            ], 422);
        }
    }

    // Anti-empalmes por especialista
    if ($this->hasOverlap($appointment->id, $data['specialist_id'], $data['start_at'], $data['end_at'])) {
        return response()->json([
            'message' => 'No se puede actualizar: ya existe una cita traslapada para ese especialista.'
        ], 422);
    }

    $appointment->update($data);

    return response()->json(['message' => 'Cita actualizada correctamente.']);
}




public function cancel(Appointment $appointment)
{
    $appointment->update([
        'status' => 'cancelled',
    ]);

    return response()->json([
        'message' => 'Cita cancelada correctamente.'
    ]);
}

public function markNoShow(Appointment $appointment)
{
    $now = now();

    // La cita debe haber iniciado hace al menos 15 minutos
    if ($now->lt($appointment->start_at->copy()->addMinutes(15))) {
        return response()->json([
            'message' => 'Solo puedes marcar inasistencia cuando hayan pasado al menos 15 minutos desde el inicio de la cita.'
        ], 422);
    }

    $appointment->update([
        'status' => 'no_show',
    ]);

    return response()->json([
        'message' => 'Cita marcada como inasistencia correctamente.'
    ]);
}




public function complete(Appointment $appointment)
{
    $appointment->update([
        'status' => 'completed',
    ]);

    return response()->json([
        'message' => 'Cita marcada como realizada correctamente.'
    ]);
}



    // Eliminar (AJAX)
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['message' => 'Cita eliminada.']);
    }

private function hasOverlap(?int $ignoreId, int $specialistId, string $startAt, string $endAt): bool
{
    return Appointment::query()
        ->where('specialist_id', $specialistId)
       ->whereNotIn('status', ['cancelled', 'no_show'])
       ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
        ->where('start_at', '<', $endAt)
        ->where('end_at', '>', $startAt)
        ->exists();
}
}
