<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\PatientPackage;
use App\Models\PatientPackageItem;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\TreatmentType;

class AppointmentController extends Controller
{
    public function index()
    {
        $patients = Patient::query()
            ->where('active', true)
            ->where('branch_id', current_branch_id())
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

            $treatments = Treatment::query()
                ->where('active', true)
                ->where('branch_id', current_branch_id())
                ->with('type:id,name,color_hex')
                ->orderBy('name')
                ->get(['id', 'name', 'treatment_type_id', 'category', 'color_hex', 'duration_minutes']);

        $specialists = User::query()
            ->whereHas('branches', function ($q) {
                $q->where('branches.id', current_branch_id());
            })
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $currentUserId = Auth::id();

                $treatmentTypes = TreatmentType::query()
    ->where('branch_id', current_branch_id())
    ->where('active', true)
    ->orderBy('name')
    ->get(['id', 'name', 'color_hex']);
    

        return view('agenda.index', compact(
            'patients',
            'treatments',
            'specialists',
            'currentUserId',
                'treatmentTypes'

        ));


    }

    public function events(Request $request)
    {
        $start = $request->query('start');
        $end   = $request->query('end');

        $appointments = Appointment::query()
            ->where('branch_id', current_branch_id())
            ->with([
            'patient:id,full_name',
            'treatment:id,name,treatment_type_id,color_hex,category',
            'treatment.type:id,name,color_hex',
            'specialist:id,name',
            'creator:id,name'
            ])
            ->when($start && $end, fn ($q) => $q->whereBetween('start_at', [$start, $end]))
            ->get();

        $events = $appointments->map(function ($a) {
            $titleParts = [];

            if ($a->patient) {
                $titleParts[] = $a->patient->full_name;
            }

            if ($a->treatment) {
                $titleParts[] = $a->treatment->name;
            }

            $title = implode(' — ', $titleParts) ?: 'Cita';

            $color = $a->treatment?->resolved_color_hex ?? '#111827';

            if (in_array($a->status, ['cancelled', 'no_show'], true)) {
                $color = '#DC2626';
            }

            return [
                'id' => (string) $a->id,
                'title' => $title,
                'start' => $a->start_at->toIso8601String(),
                'end' => $a->end_at->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'patient_id' => $a->patient_id,
                    'patient_treatment_id' => $a->patient_treatment_id,
                    'patient_package_id' => $a->patient_package_id,
                    'patient_package_item_id' => $a->patient_package_item_id,
                    'treatment_id' => $a->treatment_id,
                    'specialist_id' => $a->specialist_id,
                    'status' => $a->status,
                    'notes' => $a->notes,
                    'creator_name' => $a->creator?->name,
                    'specialist_name' => $a->specialist?->name,
                ],
            ];
        });

        return response()->json($events);
    }

    public function store(AppointmentRequest $request)
    {
        $data = $request->validated();

        $data['branch_id'] = current_branch_id();
        $data['created_by'] = Auth::id();

        $patient = Patient::query()
            ->where('branch_id', current_branch_id())
            ->find($data['patient_id'] ?? null);

        if (! $patient) {
            return response()->json([
                'message' => 'El paciente no pertenece a la sucursal activa.'
            ], 422);
        }

        if (!empty($data['patient_id']) && empty($data['patient_package_id'])) {
            return response()->json([
                'message' => 'El paciente no tiene paquetes activos disponibles.'
            ], 422);
        }

        if (!empty($data['patient_package_id']) && empty($data['patient_package_item_id'])) {
            return response()->json([
                'message' => 'Debes seleccionar un tratamiento disponible dentro del paquete.'
            ], 422);
        }

        if (!empty($data['patient_package_id']) && !empty($data['patient_package_item_id'])) {
            $resolved = $this->resolveBookablePackageItem(
                (int) $data['patient_package_id'],
                (int) $data['patient_package_item_id'],
                (int) ($data['patient_id'] ?? 0)
            );

            if (! $resolved['ok']) {
                return response()->json([
                    'message' => $resolved['message']
                ], 422);
            }

            /** @var \App\Models\PatientPackage $package */
            $package = $resolved['package'];

            /** @var \App\Models\PatientPackageItem $item */
            $item = $resolved['item'];

            $data['patient_package_id'] = $package->id;
            $data['patient_package_item_id'] = $item->id;
            $data['treatment_id'] = $item->treatment_id;
        }

        if (!empty($data['treatment_id'])) {
            $treatment = Treatment::query()
                ->where('branch_id', current_branch_id())
                ->find($data['treatment_id']);

            if (! $treatment) {
                return response()->json([
                    'message' => 'El tratamiento no pertenece a la sucursal activa.'
                ], 422);
            }
        }

        $start = Carbon::parse($data['start_at']);

        if ($start->isPast()) {
            return response()->json([
                'message' => 'No se puede crear una cita en una fecha pasada.'
            ], 422);
        }

        if ($this->hasOverlap(null, (int) $data['specialist_id'], $data['start_at'], $data['end_at'])) {
            return response()->json([
                'message' => 'No se puede agendar: ya existe una cita traslapada para ese especialista en esta sucursal.'
            ], 422);
        }

        $appointment = Appointment::create($data);

        return response()->json([
            'message' => 'Cita creada correctamente.',
            'id' => $appointment->id,
        ]);
    }

    public function update(AppointmentRequest $request, Appointment $appointment)
    {
        abort_unless((int) $appointment->branch_id === current_branch_id(), 404);

        $data = $request->validated();

        $patient = Patient::query()
            ->where('branch_id', current_branch_id())
            ->find($data['patient_id'] ?? null);

        if (! $patient) {
            return response()->json([
                'message' => 'El paciente no pertenece a la sucursal activa.'
            ], 422);
        }

        if (!empty($data['patient_id']) && empty($data['patient_package_id'])) {
            return response()->json([
                'message' => 'El paciente no tiene paquetes activos disponibles.'
            ], 422);
        }

        if (!empty($data['patient_package_id']) && empty($data['patient_package_item_id'])) {
            return response()->json([
                'message' => 'Debes seleccionar un tratamiento disponible dentro del paquete.'
            ], 422);
        }

        if (!empty($data['patient_package_id']) && !empty($data['patient_package_item_id'])) {
            $resolved = $this->resolveBookablePackageItem(
                (int) $data['patient_package_id'],
                (int) $data['patient_package_item_id'],
                (int) ($data['patient_id'] ?? 0)
            );

            if (! $resolved['ok']) {
                return response()->json([
                    'message' => $resolved['message']
                ], 422);
            }

            /** @var \App\Models\PatientPackage $package */
            $package = $resolved['package'];

            /** @var \App\Models\PatientPackageItem $item */
            $item = $resolved['item'];

            $data['patient_package_id'] = $package->id;
            $data['patient_package_item_id'] = $item->id;
            $data['treatment_id'] = $item->treatment_id;
        }

        $newStart = Carbon::parse($data['start_at']);
        $oldStart = $appointment->start_at;

        if ($oldStart->isPast()) {
            if (! $newStart->equalTo($oldStart)) {
                return response()->json([
                    'message' => 'Esta cita ya ocurrió. Puedes actualizar estatus/notas, pero no cambiar la fecha/hora.'
                ], 422);
            }
        } else {
            if ($newStart->isPast()) {
                return response()->json([
                    'message' => 'No se puede mover una cita a una fecha pasada.'
                ], 422);
            }
        }

        if ($this->hasOverlap($appointment->id, (int) $data['specialist_id'], $data['start_at'], $data['end_at'])) {
            return response()->json([
                'message' => 'No se puede actualizar: ya existe una cita traslapada para ese especialista en esta sucursal.'
            ], 422);
        }

        $appointment->update($data);

        return response()->json([
            'message' => 'Cita actualizada correctamente.'
        ]);
    }

    public function cancel(Appointment $appointment)
    {
        abort_unless((int) $appointment->branch_id === current_branch_id(), 404);

        $appointment->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Cita cancelada correctamente.'
        ]);
    }

    public function markNoShow(Appointment $appointment)
    {
        abort_unless((int) $appointment->branch_id === current_branch_id(), 404);

        $now = now();

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
        abort_unless((int) $appointment->branch_id === current_branch_id(), 404);

        $appointment->update([
            'status' => 'completed',
        ]);

        if ($appointment->patient_package_id) {
            $package = PatientPackage::query()
                ->where('branch_id', current_branch_id())
                ->with('items')
                ->find($appointment->patient_package_id);

            if ($package) {
                $package->refreshStatusIfCompleted();
            }
        }

        return response()->json([
            'message' => 'Cita marcada como realizada correctamente.'
        ]);
    }

    public function destroy(Appointment $appointment)
    {
        abort_unless((int) $appointment->branch_id === current_branch_id(), 404);

        $appointment->delete();

        return response()->json([
            'message' => 'Cita eliminada.'
        ]);
    }

    private function hasOverlap(?int $ignoreId, int $specialistId, string $startAt, string $endAt): bool
    {
        return Appointment::query()
            ->where('branch_id', current_branch_id())
            ->where('specialist_id', $specialistId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)
            ->exists();
    }

    private function resolveBookablePackageItem(int $packageId, int $packageItemId, int $patientId): array
    {
        $package = PatientPackage::query()
            ->where('branch_id', current_branch_id())
            ->with('items')
            ->find($packageId);

        if (! $package || (int) $package->patient_id !== $patientId) {
            return [
                'ok' => false,
                'message' => 'El paquete no pertenece al paciente seleccionado o a la sucursal activa.',
            ];
        }

        if ($package->status !== 'active') {
            return [
                'ok' => false,
                'message' => 'El paquete seleccionado no está activo.',
            ];
        }

        $item = $package->items()->with('treatment')->find($packageItemId);

        if (! $item) {
            return [
                'ok' => false,
                'message' => 'El tratamiento del paquete no existe.',
            ];
        }

        if ($item->remaining_sessions <= 0) {
            return [
                'ok' => false,
                'message' => 'Ese tratamiento del paquete ya no tiene sesiones disponibles.',
            ];
        }

        return [
            'ok' => true,
            'package' => $package,
            'item' => $item,
        ];
    }
}