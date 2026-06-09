<?php

use App\Http\Controllers\ConciliacionController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportacionMovimientoController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SocioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('socios', SocioController::class);
    Route::get('cuotas', [CuotaController::class, 'index'])->name('cuotas.index');
    Route::get('cuotas/exportar', [CuotaController::class, 'exportar'])->name('cuotas.exportar');
    Route::post('cuotas/generar', [CuotaController::class, 'generar'])->name('cuotas.generar');
    Route::patch('cuotas/{cuota}/pagar', [CuotaController::class, 'pagar'])->name('cuotas.pagar');
    Route::resource('movimientos', MovimientoController::class);
    Route::get('importaciones/movimientos', [ImportacionMovimientoController::class, 'index'])->name('importaciones.movimientos.index');
    Route::post('importaciones/movimientos/preview', [ImportacionMovimientoController::class, 'preview'])->name('importaciones.movimientos.preview');
    Route::post('importaciones/movimientos', [ImportacionMovimientoController::class, 'store'])->name('importaciones.movimientos.store');
    Route::get('conciliaciones', [ConciliacionController::class, 'index'])->name('conciliaciones.index');
    Route::get('conciliaciones/{movimiento}', [ConciliacionController::class, 'show'])->name('conciliaciones.show');
    Route::post('conciliaciones/{movimiento}', [ConciliacionController::class, 'confirmar'])->name('conciliaciones.confirmar');
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('reportes/{reporte}/descargar', [ReporteController::class, 'descargar'])->name('reportes.descargar');
});

require __DIR__.'/auth.php';
