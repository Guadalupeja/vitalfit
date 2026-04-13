<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Patient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class PagoController extends Controller
{
public function index(\Illuminate\Http\Request $request)
{
    $date = $request->query('date')
        ? \Carbon\Carbon::parse($request->query('date'))->toDateString()
        : now()->toDateString();

    $paymentsQuery = Payment::query()
        ->with(['patient:id,full_name'])
        ->whereDate('paid_at', $date)
        ->orderByDesc('paid_at');

    // Lista paginada (del día)
    $payments = (clone $paymentsQuery)->paginate(20)->withQueryString();

    // Totales del día
    $dailyTotal = (clone $paymentsQuery)->sum('amount');

    // Totales por método de pago
    $dailyByMethod = Payment::query()
    ->whereDate('paid_at', $date)
    ->selectRaw('method, COUNT(*) as qty, SUM(amount) as total')
    ->groupBy('method')
    ->orderByDesc('total')
    ->get();

    // Quién registró (si ya agregaste created_by a payments)
    $payments->load('creator:id,name');

    return view('pagos.index', compact('payments', 'date', 'dailyTotal', 'dailyByMethod'));
}


    public function create()
    {
        $patients = Patient::query()
            ->where('active', true)
            ->orderBy('full_name')
            ->get(['id','full_name']);

        return view('pagos.create', compact('patients'));
    }

public function store(PaymentRequest $request)
{
    $data = $request->validated();
    $data['created_by'] = Auth::id();

    if (!empty($data['patient_treatment_id'])) {
        $pkg = \App\Models\PatientTreatment::find($data['patient_treatment_id']);

        if (!$pkg || (int)$pkg->patient_id !== (int)$data['patient_id']) {
            return back()
                ->withErrors(['patient_treatment_id' => 'El paquete no pertenece al paciente seleccionado.'])
                ->withInput();
        }
    }

    if ($request->hasFile('receipt')) {
        $data['receipt_path'] = $request->file('receipt')->store('payment-receipts', 'public');
    }

    Payment::create($data);

    return redirect()
        ->route('pagos.index')
        ->with('success', 'Pago registrado correctamente.');
}

}
