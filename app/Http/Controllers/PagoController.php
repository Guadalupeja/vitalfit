<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\PatientPackage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : now()->toDateString();

        $paymentsQuery = Payment::query()
            ->with([
                'patient:id,full_name',
                'package:id,name',
                'creator:id,name',
            ])
            ->where('branch_id', current_branch_id())
            ->whereDate('paid_at', $date)
            ->orderByDesc('paid_at');

        $payments = (clone $paymentsQuery)
            ->paginate(20)
            ->withQueryString();

        $dailyTotal = (clone $paymentsQuery)->sum('amount');

        $dailyByMethod = Payment::query()
            ->where('branch_id', current_branch_id())
            ->whereDate('paid_at', $date)
            ->selectRaw('method, COUNT(*) as qty, SUM(amount) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        return view('pagos.index', compact(
            'payments',
            'date',
            'dailyTotal',
            'dailyByMethod'
        ));
    }

    public function create()
    {
        $patients = Patient::query()
            ->where('branch_id', current_branch_id())
            ->where('active', true)
            ->orderBy('full_name')
            ->get(['id', 'full_name']);

        return view('pagos.create', compact('patients'));
    }

    public function store(PaymentRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();
        $data['branch_id'] = current_branch_id();

        $patient = Patient::query()
            ->where('branch_id', current_branch_id())
            ->find($data['patient_id']);

        if (! $patient) {
            return back()
                ->withErrors([
                    'patient_id' => 'El paciente no pertenece a la sucursal activa.'
                ])
                ->withInput();
        }

        $package = PatientPackage::query()
            ->where('branch_id', current_branch_id())
            ->find($data['patient_package_id'] ?? null);

        if (! $package || (int) $package->patient_id !== (int) $data['patient_id']) {
            return back()
                ->withErrors([
                    'patient_package_id' => 'El paquete no pertenece al paciente seleccionado.'
                ])
                ->withInput();
        }

        if ($request->hasFile('receipt')) {
            $data['receipt_path'] = $request->file('receipt')->store('payment-receipts', 'public');
        }

        Payment::create([
            'branch_id' => $data['branch_id'],
            'patient_id' => $data['patient_id'],
            'patient_package_id' => $data['patient_package_id'],
            'amount' => $data['amount'],
            'method' => $data['method'],
            'paid_at' => $data['paid_at'],
            'notes' => $data['notes'] ?? null,
            'receipt_path' => $data['receipt_path'] ?? null,
            'created_by' => $data['created_by'],
        ]);

        return redirect()
            ->route('pagos.index')
            ->with('success', 'Pago registrado correctamente.');
    }
}