@extends('layouts.app')

@section('title', ' | Pagos')
@section('page_title', 'Pagos')
@section('page_subtitle', 'Registro de abonos por paciente (efectivo/transferencia/tarjeta).')

@section('page_actions')
    <a href="{{ route('pagos.create') }}" class="vf-btn-primary">
        + Registrar pago
    </a>
@endsection

@section('content')
    <div class="vf-card">
        <div class="flex flex-col gap-4 border-b border-gray-200 p-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Corte diario</h2>
                <p class="text-sm text-gray-600">Suma de pagos del día, detalle por método y pacientes.</p>
            </div>

            <form method="GET" action="{{ route('pagos.index') }}" class="flex items-end gap-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha</label>
                    <input type="date" name="date" value="{{ $date }}" class="vf-input mt-1">
                </div>

                <button class="vf-btn-primary">
                    Ver
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 gap-4 p-5 lg:grid-cols-[340px_1fr]">
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="text-sm text-gray-500">Total del día</p>
                <p class="mt-1 text-3xl font-semibold text-gray-900">${{ number_format((float)$dailyTotal, 2) }}</p>
                <p class="mt-2 text-sm text-gray-500">Fecha: {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <p class="mb-3 text-sm text-gray-500">Por método de pago</p>

                <div class="overflow-x-auto">
                    <table class="vf-table min-w-full text-sm">
                        <thead class="border-b text-left text-gray-600">
                            <tr>
                                <th class="py-2 pr-4">Método</th>
                                <th class="py-2 pr-4">Pagos</th>
                                <th class="py-2 pr-4">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                        @forelse($dailyByMethod as $row)
                            @php
                                $labels = ['cash' => 'Efectivo', 'transfer' => 'Transferencia', 'card' => 'Tarjeta'];
                            @endphp
                            <tr>
                                <td class="py-3 pr-4">{{ $labels[$row->method] ?? $row->method }}</td>
                                <td class="py-3 pr-4">{{ $row->qty }}</td>
                                <td class="py-3 pr-4 font-medium">${{ number_format((float)$row->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-6 text-center text-gray-500">No hay pagos para esta fecha.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="mt-3 text-xs text-gray-400">Tip: usa el selector de fecha para ver cortes anteriores.</p>
            </div>
        </div>
    </div>

    <div class="mt-5 vf-card">
        <div class="border-b border-gray-200 p-5">
            <p class="text-sm text-gray-600">
                Total:
                <span class="font-medium text-gray-900">{{ $payments->total() }}</span>
            </p>
        </div>

        <div class="overflow-x-auto p-5">
            <table class="vf-table min-w-full text-sm">
                <thead class="border-b text-left text-gray-600">
                    <tr>
                        <th class="py-2 pr-4">Fecha</th>
                        <th class="py-2 pr-4">Paciente</th>
                        <th class="py-2 pr-4">Monto</th>
                        <th class="py-2 pr-4">Método</th>
                        <th class="py-2 pr-4">Referencia</th>
                        <th class="py-2 pr-4">Nota</th>
                        <th class="py-2 pr-4">Comprobante</th>
                        <th class="py-2 pr-4">Registró</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @forelse($payments as $pay)
                    @php
                        $labels = ['cash' => 'Efectivo', 'transfer' => 'Transferencia', 'card' => 'Tarjeta'];
                    @endphp
                    <tr>
                        <td class="py-3 pr-4 text-gray-600">
                            {{ $pay->paid_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 pr-4 font-medium">
                            {{ $pay->patient?->full_name ?? '—' }}
                        </td>
                        <td class="py-3 pr-4 font-semibold">
                            ${{ number_format((float)$pay->amount, 2) }}
                        </td>
                        <td class="py-3 pr-4">
                            {{ $labels[$pay->method] ?? $pay->method }}
                        </td>
                        <td class="py-3 pr-4 text-gray-600">
                            {{ $pay->reference ?? '—' }}
                        </td>
                        <td class="py-3 pr-4 text-gray-600">
                            {{ $pay->note ?? '—' }}
                        </td>
                        <td class="py-3 pr-4">
                            @if($pay->receipt_path)
                                <a href="{{ asset('storage/' . $pay->receipt_path) }}" target="_blank" class="font-medium text-[var(--vf-primary)] hover:underline">
                                    Ver archivo
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4 text-gray-600">
                            {{ $pay->creator?->name ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-gray-500">Aún no hay pagos.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="border-t border-gray-200 p-5">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection