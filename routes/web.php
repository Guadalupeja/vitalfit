<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\TablaSemanalController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientPackageController;
use App\Http\Controllers\BranchSelectionController;
use App\Http\Controllers\PatientPackageApiController;
use App\Http\Controllers\PackageTemplateController;
use App\Http\Controllers\PatientPackageTemplateController;
use App\Http\Controllers\PatientPackageV2Controller;
use App\Http\Controllers\PatientPackageV2ApiController;
use App\Http\Controllers\PatientPackageItemApiController;
use App\Http\Controllers\TreatmentTypeController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas SIN sucursal seleccionada
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/seleccionar-sucursal', [BranchSelectionController::class, 'show'])
        ->name('branches.select');

    Route::post('/seleccionar-sucursal', [BranchSelectionController::class, 'select'])
        ->name('branches.select.store');
});

/*
|--------------------------------------------------------------------------
| Rutas autenticadas CON sucursal seleccionada
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'branch.selected'])->group(function () {
    Route::get('/agenda', [AppointmentController::class, 'index'])->name('agenda.index');

    // API para FullCalendar
    Route::get('/api/agenda/events', [AppointmentController::class, 'events'])->name('agenda.events');
    Route::post('/api/agenda/appointments', [AppointmentController::class, 'store'])->name('agenda.appointments.store');
    Route::put('/api/agenda/appointments/{appointment}', [AppointmentController::class, 'update'])->name('agenda.appointments.update');
    Route::delete('/api/agenda/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('agenda.appointments.destroy');

    Route::post('/api/agenda/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
        ->name('agenda.appointments.cancel');

    Route::post('/api/agenda/appointments/{appointment}/no-show', [AppointmentController::class, 'markNoShow'])
        ->name('agenda.appointments.no_show');

    Route::post('/api/agenda/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])
        ->name('agenda.appointments.complete');

    Route::resource('pacientes', PacienteController::class)
        ->parameters(['pacientes' => 'paciente'])
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

Route::get('/pagos', [PagoController::class, 'index'])->name('pagos.index');
Route::get('/pagos/crear', [PagoController::class, 'create'])->name('pagos.create');
Route::post('/pagos', [PagoController::class, 'store'])->name('pagos.store');
Route::get('/pagos/{pago}/editar', [PagoController::class, 'edit'])->name('pagos.edit');
Route::put('/pagos/{pago}', [PagoController::class, 'update'])->name('pagos.update');
Route::delete('/pagos/{pago}', [PagoController::class, 'destroy'])->name('pagos.destroy');

    Route::middleware(['admin'])->group(function () {
    Route::get('/tabla-semanal', [TablaSemanalController::class, 'index'])
        ->name('tabla_semanal.index');
});
    Route::resource('tratamientos', TreatmentController::class)
        ->parameters(['tratamientos' => 'tratamiento'])
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Catálogo reusable de paquetes
    Route::resource('paquetes', PackageTemplateController::class)
        ->parameters(['paquetes' => 'paquete'])
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // API de paquetes del paciente
    Route::get('/api/pacientes/{patient}/paquetes', [PatientPackageApiController::class, 'index'])
        ->name('api.pacientes.paquetes');

    // Paquetes asignados al paciente
    Route::post('/pacientes/{patient}/paquetes', [PatientPackageController::class, 'store'])
        ->name('pacientes.paquetes.store');

    Route::put('/paquetes-paciente/{package}', [PatientPackageController::class, 'update'])
        ->name('patient_packages.update');

    Route::delete('/paquetes-paciente/{package}', [PatientPackageController::class, 'destroy'])
        ->name('patient_packages.destroy');

    Route::get('/dashboard', function () {
        return redirect()->route('agenda.index');
    })->name('dashboard');


Route::post('/pacientes/{patient}/paquetes-desde-catalogo', [PatientPackageTemplateController::class, 'store'])
    ->name('pacientes.paquetes_desde_catalogo.store');
Route::put('/pacientes-paquetes/{patientPackage}', [PatientPackageV2Controller::class, 'update'])
    ->name('patient_packages_v2.update');

Route::delete('/pacientes-paquetes/{patientPackage}', [PatientPackageV2Controller::class, 'destroy'])
    ->name('patient_packages_v2.destroy');

Route::get('/api/pacientes/{patient}/paquetes-v2', [PatientPackageV2ApiController::class, 'index'])
    ->name('api.pacientes.paquetes_v2');

Route::get('/api/paquetes-paciente/{patientPackage}/items', [PatientPackageItemApiController::class, 'index'])
    ->name('api.paquetes_paciente.items');

    Route::get('/api/pacientes/{patient}/paquetes-v2', [PatientPackageV2ApiController::class, 'index'])
    ->name('api.pacientes.paquetes_v2');

Route::resource('tipos-tratamiento', TreatmentTypeController::class)
    ->parameters(['tipos-tratamiento' => 'tipo_tratamiento'])
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

Route::middleware(['admin'])->group(function () {
    Route::resource('usuarios', UserController::class)
        ->parameters(['usuarios' => 'usuario'])
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});

Route::get('/api/agenda/available-specialists', [AppointmentController::class, 'availableSpecialists'])
    ->name('agenda.available_specialists');

});

require __DIR__ . '/auth.php';