@extends('layouts.app')

@section('title', ' | Pagos')
@section('page_title', 'Pagos')
@section('page_subtitle', 'Registro de abonos por paciente (efectivo/transferencia/tarjeta).')

@section('page_actions')
    <a href="{{ route('pagos.create') }}"
       class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
        + Registrar pago
    </a>
@endsection

@section('content')
    {{-- =========================
         CORTE DIARIO (NUEVO)
         ========================= --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-4">
        <div class="p-5 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                <div>
                    <p class="font-semibold text-gray-900">Corte diario</p>
                    <p class="text-sm text-gray-600">Suma de pagos del día, detalle por método y pacientes.</p>
                </div>

                <form method="GET" action="{{ route('pagos.index') }}" class="flex items-end gap-2">
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Fecha</label>
                        <input type="date"
                               name="date"
                               value="{{ $date ?? now()->toDateString() }}"
                               class="mt-1 rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                    </div>

                    <button class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white hover:bg-gray-800">
                        Ver
                    </button>
                </form>
            </div>
        </div>

        <div class="p-5 grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500">Total del día</p>
                <p class="text-xl font-semibold text-gray-900">
                    ${{ number_format((float)($dailyTotal ?? 0), 2) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Fecha: <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($date ?? now())->format('d/m/Y') }}</span>
                </p>
            </div>

            <div class="lg:col-span-2 rounded-lg border border-gray-200 p-4">
                <p class="text-xs text-gray-500 mb-2">Por método de pago</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-600 border-b">
                            <tr>
                                <th class="py-2 pr-4">Método</th>
                                <th class="py-2 pr-4">Pagos</th>
                                <th class="py-2 pr-4">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @php
                                $labels = ['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta'];
                            @endphp

                            @forelse(($dailyByMethod ?? collect()) as $row)
                                <tr>
                                    <td class="py-2 pr-4">
                                        {{ $labels[$row->method] ?? ($row->method ?? '—') }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        {{ $row->qty ?? 0 }}
                                    </td>
                                    <td class="py-2 pr-4 font-medium">
                                        ${{ number_format((float)($row->total ?? 0), 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-gray-500">
                                        Sin pagos ese día.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-500 mt-3">
                    Tip: usa el selector de fecha para ver cortes anteriores.
                </p>
            </div>
        </div>
    </div>

    {{-- =========================
         LISTADO DE PAGOS (EXISTENTE)
         ========================= --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-200 flex items-center justify-between">
            <p class="text-sm text-gray-600">
                Total: <span class="font-medium text-gray-900">{{ $payments->total() }}</span>
            </p>
        </div>

        <div class="p-5 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left text-gray-600 border-b">
                    <tr>
                        <th class="py-2 pr-4">Fecha</th>
                        <th class="py-2 pr-4">Paciente</th>
                        <th class="py-2 pr-4">Monto</th>
                        <th class="py-2 pr-4">Método</th>
                        <th class="py-2 pr-4">Referencia</th>
                        <th class="py-2 pr-4">Nota</th>
                        <th class="py-2 pr-4">Comprobante</th>

                        {{-- NUEVO (opcional): quién registró --}}
                        <th class="py-2 pr-4">Registró</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                @forelse($payments as $pay)
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
                            @php
                                $labels = ['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta'];
                            @endphp
                            {{ $labels[$pay->method] ?? $pay->method }}
                        </td>

                        <td class="py-3 pr-4 text-gray-600">
                            {{ $pay->reference ?? '—' }}
                        </td>

                        <td class="py-3 pr-4 text-gray-600">
                            {{ $pay->note ?? '—' }}
                        </td>

                        <td class="py-3 pr-4 text-gray-600">
                            @if($pay->receipt_path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($pay->receipt_path) }}"
                                target="_blank"
                                class="font-medium text-gray-900 hover:underline">
                                    Ver archivo
                                </a>
                            @else
                                —
                            @endif
                        </td>

                        {{-- NUEVO: creador del pago --}}
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
            <div class="p-5 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection
