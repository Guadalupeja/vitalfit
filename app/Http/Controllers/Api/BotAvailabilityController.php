<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
        ]);

        $branch = Branch::query()
            ->where('active', true)
            ->findOrFail($data['branch_id']);

        $date = Carbon::createFromFormat('Y-m-d', $data['date'])->startOfDay();
        $durationMin = (int) $data['duration_min'];

        if ($date->isPast() && !$date->isToday()) {
            return response()->json([
                'error' => [
                    'code' => 'past_date',
                    'message' => 'No se puede consultar disponibilidad de una fecha pasada.',
                ],
            ], 422);
        }

        $specialistIds = User::query()
            ->where('active', true)
            ->whereIn('role', ['admin', 'specialist'])
            ->whereHas('branches', function ($query) use ($branch) {
                $query->where('branches.id', $branch->id);
            })
            ->pluck('id');

        if ($specialistIds->isEmpty()) {
            return response()->json([
                'data' => [
                    'branch_id' => $branch->id,
                    'branch_name' => $branch->name,
                    'date' => $date->format('Y-m-d'),
                    'duration_min' => $durationMin,
                    'slots' => [],
                ],
            ]);
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
                return !$busyAppointments->contains(function ($appointment) use ($specialistId, $slotStart, $slotEnd) {
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

        return response()->json([
            'data' => [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'date' => $date->format('Y-m-d'),
                'duration_min' => $durationMin,
                'slots' => $slots,
            ],
        ]);
    }
}