@extends('layouts.app')

@section('title', ' | Registrar pago')
@section('page_title', 'Registrar pago')
@section('page_subtitle', 'Agregar un abono a un paciente.')

@section('content')
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('pagos.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

{{-- Paciente --}}
<div>
  <label class="block text-sm font-medium text-gray-700">Paciente</label>
  <select name="patient_id" id="patient_id"
          class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
      <option value="">— Selecciona —</option>
      @foreach($patients as $p)
        <option value="{{ $p->id }}">{{ $p->full_name }}</option>
      @endforeach
  </select>
</div>

{{-- Paquete --}}
<div>
  <label class="block text-sm font-medium text-gray-700">Paquete del paciente</label>
  <select id="patient_treatment_id" name="patient_treatment_id"
          data-template-url="{{ route('api.pacientes.paquetes', ['patient' => 0]) }}"
          class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
      <option value="">— Selecciona paciente primero —</option>
  </select>
  @error('patient_treatment_id')
    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
  @enderror
</div>





            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Fecha y hora</label>
                    <input type="datetime-local" name="paid_at"
                           value="{{ old('paid_at', now()->format('Y-m-d\\TH:i')) }}"
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                    @error('paid_at') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Monto</label>
                    <input type="number" step="0.01" min="0.01" name="amount"
                           value="{{ old('amount') }}"
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                    @error('amount') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Método</label>
                    <select name="method"
                            class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                        <option value="cash" @selected(old('method')==='cash')>Efectivo</option>
                        <option value="transfer" @selected(old('method')==='transfer')>Transferencia</option>
                        <option value="card" @selected(old('method')==='card')>Tarjeta</option>
                    </select>
                    @error('method') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Referencia (opcional)</label>
                    <input type="text" name="reference"
                           value="{{ old('reference') }}"
                           class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">
                    @error('reference') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Nota (opcional)</label>
                <textarea name="note" rows="3"
                          class="mt-1 w-full rounded-md border-gray-300 focus:border-gray-900 focus:ring-gray-900">{{ old('note') }}</textarea>
                @error('note') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>



            <div>
                <label class="block text-sm font-medium text-gray-700">Comprobante (opcional)</label>
                <input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf,.webp"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700">
                @error('receipt') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-1">
                    Formatos permitidos: JPG, PNG, WEBP o PDF. Máximo 4 MB.
                </p>
            </div>


            <div class="flex items-center gap-2">
                <a href="{{ route('pagos.index') }}"
                   class="rounded-md border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                    Cancelar
                </a>

                <button type="submit"
                        class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Guardar
                </button>
            </div>
        </form>
    </div>
@endsection
