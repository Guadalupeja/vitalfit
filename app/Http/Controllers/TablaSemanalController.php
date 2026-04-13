<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientTreatment;
use App\Models\Payment;
use App\Models\Treatment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TablaSemanalController extends Controller
{
    public function index(Request $request)
    {
        $weekStart = $request->query('week_start')
            ? Carbon::parse($request->query('week_start'))->startOfDay()
            : now()->startOfWeek(Carbon::MONDAY)->startOfDay();

        $weekEnd = (clone $weekStart)->addDays(6)->endOfDay();

        $monthStart = (clone $weekStart)->startOfMonth()->startOfDay();
        $monthEnd = (clone $weekStart)->endOfMonth()->endOfDay();

        // Filtros
        $specialistId = $request->query('specialist_id');
        $treatmentId = $request->query('treatment_id');
        $status = $request->query('status');

        // Catálogos para filtros
        $specialists = User::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $treatments = Treatment::query()
            ->where('active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'color_hex']);

        // =========================
        // PAQUETES ACTIVOS
        // =========================
        $packages = PatientTreatment::query()
            ->with([
                'patient:id,full_name,phone',
                'treatment:id,name,color_hex',
            ])
            ->where('status', 'active')
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->withSum(['payments as total_paid' => function ($q) use ($weekStart, $weekEnd) {
                // acumulado total del paquete, no filtrado por semana
            }], 'amount')
            ->withCount(['appointments as completed_sessions' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->orderBy('patient_id')
            ->orderByDesc('id')
            ->get();

        // =========================
        // CITAS DE LA SEMANA AGRUPADAS POR PAQUETE
        // =========================
        $appointmentsByPackage = Appointment::query()
            ->with(['treatment:id,name,color_hex', 'specialist:id,name'])
            ->whereNotNull('patient_treatment_id')
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_at')
            ->get()
            ->groupBy('patient_treatment_id');

        // =========================
        // ESTADÍSTICAS SEMANALES
        // =========================
        $weeklyIncome = Payment::query()
            ->when($treatmentId, function ($q) use ($treatmentId) {
                $q->whereHas('package', fn($sub) => $sub->where('treatment_id', $treatmentId));
            })
            ->whereBetween('paid_at', [$weekStart, $weekEnd])
            ->sum('amount');

        $weeklyTopTreatmentsByIncome = Payment::query()
            ->join('patient_treatments', 'patient_treatments.id', '=', 'payments.patient_treatment_id')
            ->join('treatments', 'treatments.id', '=', 'patient_treatments.treatment_id')
            ->whereBetween('payments.paid_at', [$weekStart, $weekEnd])
            ->when($treatmentId, fn($q) => $q->where('treatments.id', $treatmentId))
            ->groupBy('treatments.id', 'treatments.name')
            ->select([
                'treatments.id',
                'treatments.name',
                DB::raw('COUNT(payments.id) as payments_count'),
                DB::raw('SUM(payments.amount) as total_income'),
            ])
            ->orderByDesc('total_income')
            ->get();

        $weeklyCompletedByTreatment = Appointment::query()
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->whereNotNull('treatment_id')
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->join('treatments', 'treatments.id', '=', 'appointments.treatment_id')
            ->groupBy('treatments.id', 'treatments.name')
            ->select([
                'treatments.id',
                'treatments.name',
                DB::raw('COUNT(appointments.id) as completed_count'),
            ])
            ->orderByDesc('completed_count')
            ->get();

        $weeklyCancelledAppointments = Appointment::query()
            ->with(['patient:id,full_name', 'treatment:id,name', 'specialist:id,name'])
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->whereIn('status', ['cancelled', 'no_show'])
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_at')
            ->get();

        // =========================
        // ESTADÍSTICAS MENSUALES
        // =========================
        $monthlyIncome = Payment::query()
            ->when($treatmentId, function ($q) use ($treatmentId) {
                $q->whereHas('package', fn($sub) => $sub->where('treatment_id', $treatmentId));
            })
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthlyTopTreatmentsByIncome = Payment::query()
            ->join('patient_treatments', 'patient_treatments.id', '=', 'payments.patient_treatment_id')
            ->join('treatments', 'treatments.id', '=', 'patient_treatments.treatment_id')
            ->whereBetween('payments.paid_at', [$monthStart, $monthEnd])
            ->when($treatmentId, fn($q) => $q->where('treatments.id', $treatmentId))
            ->groupBy('treatments.id', 'treatments.name')
            ->select([
                'treatments.id',
                'treatments.name',
                DB::raw('COUNT(payments.id) as payments_count'),
                DB::raw('SUM(payments.amount) as total_income'),
            ])
            ->orderByDesc('total_income')
            ->get();

        $monthlyCompletedByTreatment = Appointment::query()
            ->whereBetween('start_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->whereNotNull('treatment_id')
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->join('treatments', 'treatments.id', '=', 'appointments.treatment_id')
            ->groupBy('treatments.id', 'treatments.name')
            ->select([
                'treatments.id',
                'treatments.name',
                DB::raw('COUNT(appointments.id) as completed_count'),
            ])
            ->orderByDesc('completed_count')
            ->get();

        $monthlyCancelledAppointments = Appointment::query()
            ->with(['patient:id,full_name', 'treatment:id,name', 'specialist:id,name'])
            ->whereBetween('start_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['cancelled', 'no_show'])
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_at')
            ->get();

        return view('tabla-semanal.index', [
            'packages' => $packages,
            'appointmentsByPackage' => $appointmentsByPackage,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,

            'specialists' => $specialists,
            'treatments' => $treatments,
            'specialistId' => $specialistId,
            'treatmentId' => $treatmentId,
            'status' => $status,

            'weeklyIncome' => $weeklyIncome,
            'weeklyTopTreatmentsByIncome' => $weeklyTopTreatmentsByIncome,
            'weeklyCompletedByTreatment' => $weeklyCompletedByTreatment,
            'weeklyCancelledAppointments' => $weeklyCancelledAppointments,

            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'monthlyIncome' => $monthlyIncome,
            'monthlyTopTreatmentsByIncome' => $monthlyTopTreatmentsByIncome,
            'monthlyCompletedByTreatment' => $monthlyCompletedByTreatment,
            'monthlyCancelledAppointments' => $monthlyCancelledAppointments,
        ]);
    }
}