<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\TablaSemanalController;
use App\Http\Controllers\TratamientoController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientPackageController;
use App\Http\Controllers\BranchSelectionController;





Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'branch.selected'])->group(function () {
   Route::get('/agenda', [AppointmentController::class, 'index'])->name('agenda.index');

// API para FullCalendar
    Route::get('/api/agenda/events', [AppointmentController::class, 'events'])->name('agenda.events');
    Route::post('/api/agenda/appointments', [AppointmentController::class, 'store'])->name('agenda.appointments.store');
    Route::put('/api/agenda/appointments/{appointment}', [AppointmentController::class, 'update'])->name('agenda.appointments.update');
    Route::delete('/api/agenda/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('agenda.appointments.destroy');
   Route::resource('pacientes', PacienteController::class)
    ->parameters(['pacientes' => 'paciente'])
    ->only(['index','create','store','edit','update','destroy']);
    Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::get('/pagos/crear', [PagoController::class, 'create'])->name('pagos.create');
    Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::get('/tabla-semanal', [TablaSemanalController::class, 'index'])->name('tabla_semanal.index');

    // Puedes usar /dashboard de Breeze o redirigirlo a /agenda:
    Route::get('/dashboard', function () {
        return redirect()->route('agenda.index');
    })->name('dashboard');
        Route::resource('tratamientos', TreatmentController::class)
        ->parameters(['tratamientos' => 'tratamiento'])
        ->only(['index','create','store','edit','update','destroy']);

        Route::get('/api/pacientes/{patient}/paquetes', [\App\Http\Controllers\PatientPackageApiController::class, 'index'])
  ->name('api.pacientes.paquetes');

  Route::post('/pacientes/{patient}/paquetes', [PatientPackageController::class, 'store'])
    ->name('pacientes.paquetes.store');

Route::put('/paquetes/{package}', [PatientPackageController::class, 'update'])
    ->name('paquetes.update');

Route::delete('/paquetes/{package}', [PatientPackageController::class, 'destroy'])
    ->name('paquetes.destroy');
    



Route::post('/api/agenda/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
    ->name('agenda.appointments.cancel');

Route::post('/api/agenda/appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow'])
    ->name('agenda.appointments.no_show');
    
Route::post('/api/agenda/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])
    ->name('agenda.appointments.complete');


Route::get('/seleccionar-sucursal', [BranchSelectionController::class, 'show'])
    ->name('branches.select');

Route::post('/seleccionar-sucursal', [BranchSelectionController::class, 'select'])
    ->name('branches.select.store');


});

require __DIR__.'/auth.php';
