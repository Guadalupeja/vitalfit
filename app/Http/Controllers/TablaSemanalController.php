<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\PatientPackage;
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

        $specialistId = $request->query('specialist_id');
        $treatmentId = $request->query('treatment_id');
        $status = $request->query('status');

        $specialists = User::query()
            ->whereHas('branches', function ($q) {
                $q->where('branches.id', current_branch_id());
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $treatments = Treatment::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'color_hex']);

        $packages = PatientPackage::query()
            ->where('branch_id', current_branch_id())
            ->with([
                'patient' => function ($query) {
                    $query->select('id', 'full_name', 'phone')
                        ->withCount([
                            'packagesNew as packages_count' => function ($sub) {
                                $sub->where('branch_id', current_branch_id());
                            }
                        ]);
                },
                'items.treatment:id,name,color_hex',
            ])
            ->when($treatmentId, function ($q) use ($treatmentId) {
                $q->whereHas('items', function ($sub) use ($treatmentId) {
                    $sub->where('treatment_id', $treatmentId);
                });
            })
            ->orderByDesc('id')
            ->get();

        $appointmentsByPackage = Appointment::query()
            ->where('branch_id', current_branch_id())
            ->with(['treatment:id,name,color_hex', 'specialist:id,name'])
            ->whereNotNull('patient_package_id')
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_at')
            ->get()
            ->groupBy('patient_package_id');

        $paymentsByPackage = Payment::query()
            ->where('branch_id', current_branch_id())
            ->whereNotNull('patient_package_id')
            ->select('patient_package_id', DB::raw('SUM(amount) as total_paid'))
            ->groupBy('patient_package_id')
            ->pluck('total_paid', 'patient_package_id');

        $weeklyIncome = Payment::query()
            ->where('branch_id', current_branch_id())
            ->whereNotNull('patient_package_id')
            ->whereBetween('paid_at', [$weekStart, $weekEnd])
            ->when($treatmentId, function ($q) use ($treatmentId) {
                $q->whereHas('package.items', function ($sub) use ($treatmentId) {
                    $sub->where('treatment_id', $treatmentId);
                });
            })
            ->sum('amount');

        $monthlyIncome = Payment::query()
            ->where('branch_id', current_branch_id())
            ->whereNotNull('patient_package_id')
            ->whereBetween('paid_at', [$monthStart, $monthEnd])
            ->when($treatmentId, function ($q) use ($treatmentId) {
                $q->whereHas('package.items', function ($sub) use ($treatmentId) {
                    $sub->where('treatment_id', $treatmentId);
                });
            })
            ->sum('amount');
$weeklyTopPackagesByIncome = $this->buildTopPackagesByIncome($weekStart, $weekEnd, $treatmentId);
$monthlyTopPackagesByIncome = $this->buildTopPackagesByIncome($monthStart, $monthEnd, $treatmentId);


        $weeklyCompletedByTreatment = Appointment::query()
            ->where('appointments.branch_id', current_branch_id())
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

        $monthlyCompletedByTreatment = Appointment::query()
            ->where('appointments.branch_id', current_branch_id())
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

        $weeklyCancelledAppointments = Appointment::query()
            ->where('branch_id', current_branch_id())
            ->with(['patient:id,full_name', 'treatment:id,name', 'specialist:id,name'])
            ->whereBetween('start_at', [$weekStart, $weekEnd])
            ->whereIn('status', ['cancelled', 'no_show'])
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_at')
            ->get();

        $monthlyCancelledAppointments = Appointment::query()
            ->where('branch_id', current_branch_id())
            ->with(['patient:id,full_name', 'treatment:id,name', 'specialist:id,name'])
            ->whereBetween('start_at', [$monthStart, $monthEnd])
            ->whereIn('status', ['cancelled', 'no_show'])
            ->when($specialistId, fn($q) => $q->where('specialist_id', $specialistId))
            ->when($treatmentId, fn($q) => $q->where('treatment_id', $treatmentId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('start_at')
            ->get();

        // Filtrar solo actividad semanal:
        // 1) paquete asignado en semana
        // 2) o con al menos una cita en semana
        $packages = $packages->filter(function ($pkg) use ($weekStart, $weekEnd, $appointmentsByPackage) {
            $startedThisWeek = $pkg->started_on
                ? Carbon::parse($pkg->started_on)->between($weekStart, $weekEnd)
                : false;

            $hasAppointmentsThisWeek = ($appointmentsByPackage[$pkg->id] ?? collect())->isNotEmpty();

            return $startedThisWeek || $hasAppointmentsThisWeek;
        })->values();

        $activityByPackage = $packages->mapWithKeys(function ($pkg) use ($weekStart, $weekEnd, $appointmentsByPackage) {
            $startedThisWeek = $pkg->started_on
                ? Carbon::parse($pkg->started_on)->between($weekStart, $weekEnd)
                : false;

            $hasAppointmentsThisWeek = ($appointmentsByPackage[$pkg->id] ?? collect())->isNotEmpty();

            if ($startedThisWeek && $hasAppointmentsThisWeek) {
                $label = 'Cita + nueva asignación';
            } elseif ($startedThisWeek) {
                $label = 'Nueva asignación';
            } else {
                $label = 'Cita';
            }

            return [$pkg->id => $label];
        });

        return view('tabla-semanal.index', [
            'packages' => $packages,
            'appointmentsByPackage' => $appointmentsByPackage,
            'paymentsByPackage' => $paymentsByPackage,
            'activityByPackage' => $activityByPackage,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,

            'specialists' => $specialists,
            'treatments' => $treatments,
            'specialistId' => $specialistId,
            'treatmentId' => $treatmentId,
            'status' => $status,

            'weeklyIncome' => $weeklyIncome,
'weeklyTopPackagesByIncome' => $weeklyTopPackagesByIncome,           
 'weeklyCompletedByTreatment' => $weeklyCompletedByTreatment,
            'weeklyCancelledAppointments' => $weeklyCancelledAppointments,

            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'monthlyIncome' => $monthlyIncome,
'monthlyTopPackagesByIncome' => $monthlyTopPackagesByIncome,            
 'monthlyCompletedByTreatment' => $monthlyCompletedByTreatment,
            'monthlyCancelledAppointments' => $monthlyCancelledAppointments,
        ]);
    }

private function buildTopPackagesByIncome(Carbon $start, Carbon $end, ?string $treatmentId = null)
{
    return Payment::query()
        ->where('payments.branch_id', current_branch_id())
        ->whereNotNull('payments.patient_package_id')
        ->whereBetween('payments.paid_at', [$start, $end])
        ->join('patient_packages', 'patient_packages.id', '=', 'payments.patient_package_id')
        ->when($treatmentId, function ($q) use ($treatmentId) {
            $q->whereExists(function ($sub) use ($treatmentId) {
                $sub->select(DB::raw(1))
                    ->from('patient_package_items')
                    ->whereColumn('patient_package_items.patient_package_id', 'patient_packages.id')
                    ->where('patient_package_items.treatment_id', $treatmentId);
            });
        })
        ->groupBy('patient_packages.name')
        ->select([
            DB::raw('MIN(patient_packages.id) as id'),
            'patient_packages.name',
            DB::raw('COUNT(payments.id) as payments_count'),
            DB::raw('COUNT(DISTINCT patient_packages.id) as packages_count'),
            DB::raw('SUM(payments.amount) as total_income'),
        ])
        ->orderByDesc('total_income')
        ->limit(5)
        ->get();
}
}