<?php
// ================================================================
// routes/web.php
// ================================================================

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TecnicoController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\RepuestoController;
use Illuminate\Support\Facades\Route;

// Ruta raíz redirige al dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard principal
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// CRUD de Clientes
Route::resource('clientes', ClienteController::class);

// CRUD de Técnicos
Route::resource('tecnicos', TecnicoController::class);

// CRUD de Equipos
Route::resource('equipos', EquipoController::class);

// CRUD de Servicios
Route::resource('servicios', ServicioController::class);
Route::post('servicios/{servicio}/asignar-tecnico', [ServicioController::class, 'asignarTecnico'])
    ->name('servicios.asignar-tecnico');
Route::post('servicios/{servicio}/cambiar-estado', [ServicioController::class, 'cambiarEstado'])
    ->name('servicios.cambiar-estado');

// CRUD de Marcas
Route::resource('marcas', MarcaController::class)->except(['show']);

// CRUD de Repuestos
Route::resource('repuestos', RepuestoController::class);