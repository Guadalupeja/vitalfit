<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\SpecialistSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class BotAvailabilityController extends Controller
{
    public function branches()
    {
        $branches = Branch::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'data' => $branches,
        ]);
    }

    public function availability(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('active', true)],
            'date' => ['required', 'date_format:Y-m-d'],
            'duration_min' => ['required', 'integer', Rule::in([30, 40, 60, 90, 120])],
            'service_type' => ['nullable', Rule::in(['general', 'nutrition'])],
        ]);

        $branch = Branch::query()
            ->where('active', true)
            ->findOrFail($data['branch_id']);

        $date = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();
        $durationMin = (int) $data['duration_min'];
        $serviceType = $data['service_type'] ?? 'general';

        if ($date->isPast() && ! $date->isToday()) {
            return response()->json([
                'error' => [
                    'code' => 'past_date',
                    'message' => 'No se puede consultar disponibilidad de una fecha pasada.',
                ],
            ], 422);
        }

        if ($serviceType === 'nutrition') {
            $slots = $this->nutritionSlots($branch, $date, $durationMin);
        } else {
            $slots = $this->generalSlots($branch, $date, $durationMin);
        }

        return response()->json([
            'data' => [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'date' => $date->format('Y-m-d'),
                'duration_min' => $durationMin,
                'service_type' => $serviceType,
                'slots' => $slots,
            ],
        ]);
    }

    private function generalSlots(Branch $branch, Carbon $date, int $durationMin): array
    {
        $specialistIds = User::query()
            ->where('active', true)
            ->where('role', 'specialist')
            ->whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id);
            })
            ->pluck('id');

        if ($specialistIds->isEmpty()) {
            return [];
        }

        $opening = $date->copy()->setTime(9, 0);
        $closing = $date->copy()->setTime(19, 0);

        $latestStart = $closing->copy()->subMinutes($durationMin);

        if ($durationMin >= 90) {
            $latestStart = min($latestStart, $date->copy()->setTime(17, 30));
        } else {
            $latestStart = min($latestStart, $date->copy()->setTime(18, 0));
        }

        $busyAppointments = Appointment::query()
            ->where('branch_id', $branch->id)
            ->whereIn('specialist_id', $specialistIds)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereDate('start_at', $date->format('Y-m-d'))
            ->get(['specialist_id', 'start_at', 'end_at']);

        $slots = [];

        for ($slotStart = $opening->copy(); $slotStart->lte($latestStart); $slotStart->addMinutes(30)) {
            if ($slotStart->isPast()) {
                continue;
            }

            $slotEnd = $slotStart->copy()->addMinutes($durationMin);

            $hasAvailableSpecialist = $specialistIds->contains(function ($specialistId) use ($busyAppointments, $slotStart, $slotEnd) {
                return ! $busyAppointments->contains(function ($appointment) use ($specialistId, $slotStart, $slotEnd) {
                    if ((int) $appointment->specialist_id !== (int) $specialistId) {
                        return false;
                    }

                    return $appointment->start_at->lt($slotEnd)
                        && $appointment->end_at->gt($slotStart);
                });
            });

            if ($hasAvailableSpecialist) {
                $slots[] = $slotStart->format('H:i');
            }
        }

        return $slots;
    }

    private function nutritionSlots(Branch $branch, Carbon $date, int $durationMin): array
    {
        $nutritionUserId = (int) config('services.vitalfit_bot.nutrition_user_id');

        if ($nutritionUserId <= 0) {
            return [];
        }

        $nutritionUserExists = User::query()
            ->where('id', $nutritionUserId)
            ->where('active', true)
            ->exists();

        if (! $nutritionUserExists) {
            return [];
        }

        $weekday = $date->dayOfWeekIso;

        $schedules = SpecialistSchedule::query()
            ->where('branch_id', $branch->id)
            ->where('user_id', $nutritionUserId)
            ->where('weekday', $weekday)
            ->where('service_type', 'nutrition')
            ->where('active', true)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        if ($schedules->isEmpty()) {
            return [];
        }

        $busyAppointments = Appointment::query()
            ->where('specialist_id', $nutritionUserId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereDate('start_at', $date->format('Y-m-d'))
            ->get(['specialist_id', 'start_at', 'end_at']);

        $slots = collect();

        foreach ($schedules as $schedule) {
            $opening = Carbon::createFromFormat(
                'Y-m-d H:i',
                $date->format('Y-m-d') . ' ' . substr((string) $schedule->start_time, 0, 5)
            );

            $closing = Carbon::createFromFormat(
                'Y-m-d H:i',
                $date->format('Y-m-d') . ' ' . substr((string) $schedule->end_time, 0, 5)
            );

            $latestStart = $closing->copy()->subMinutes($durationMin);

            for ($slotStart = $opening->copy(); $slotStart->lte($latestStart); $slotStart->addMinutes(30)) {
                if ($slotStart->isPast()) {
                    continue;
                }

                $slotEnd = $slotStart->copy()->addMinutes($durationMin);

                $hasOverlap = $busyAppointments->contains(function ($appointment) use ($slotStart, $slotEnd) {
                    return $appointment->start_at->lt($slotEnd)
                        && $appointment->end_at->gt($slotStart);
                });

                if (! $hasOverlap) {
                    $slots->push($slotStart->format('H:i'));
                }
            }
        }

        return $slots
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}