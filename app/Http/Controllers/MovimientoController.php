<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimientoRequest;
use App\Models\Cuota;
use App\Models\Movimiento;
use App\Models\Socio;

class MovimientoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movimientos = Movimiento::with(['socio', 'cuota'])
            ->when(request('tipo'), fn ($query, $tipo) => $query->where('tipo', $tipo))
            ->when(request('tipo_deposito'), fn ($query, $tipoDeposito) => $query->where('tipo_deposito', $tipoDeposito))
            ->when(request('estado_conciliacion'), fn ($query, $estado) => $query->where('estado_conciliacion', $estado))
            ->when(request('socio_id'), fn ($query, $socioId) => $query->where('socio_id', $socioId))
            ->when(request('desde'), fn ($query, $desde) => $query->whereDate('fecha', '>=', $desde))
            ->when(request('hasta'), fn ($query, $hasta) => $query->whereDate('fecha', '<=', $hasta))
            ->latest('fecha')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('movimientos.index', [
            'movimientos' => $movimientos,
            'socios' => Socio::orderBy('nombre')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('movimientos.create', $this->formData(new Movimiento([
            'fecha' => today(),
            'tipo' => 'ingreso',
            'fuente' => 'manual',
        ])));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMovimientoRequest $request)
    {
        Movimiento::create($request->validated() + [
            'fuente' => 'manual',
            'estado_conciliacion' => $request->filled('socio_id') ? 'conciliado' : 'pendiente',
        ]);

        return redirect()
            ->route('movimientos.index')
            ->with('status', 'Movimiento registrado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Movimiento $movimiento)
    {
        $movimiento->load(['socio', 'cuota.socio']);

        return view('movimientos.show', compact('movimiento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movimiento $movimiento)
    {
        return view('movimientos.edit', $this->formData($movimiento->load(['socio', 'cuota'])));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreMovimientoRequest $request, Movimiento $movimiento)
    {
        $movimiento->update($request->validated());

        return redirect()
            ->route('movimientos.show', $movimiento)
            ->with('status', 'Movimiento actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movimiento $movimiento)
    {
        $movimiento->delete();

        return redirect()
            ->route('movimientos.index')
            ->with('status', 'Movimiento eliminado correctamente.');
    }

    private function formData(Movimiento $movimiento): array
    {
        return [
            'movimiento' => $movimiento,
            'socios' => Socio::where('estado', 'activo')->orderBy('nombre')->get(),
            'cuotas' => Cuota::with('socio')
                ->where(function ($query) use ($movimiento) {
                    $query->whereIn('estado', ['pendiente', 'parcial']);

                    if ($movimiento->cuota_id) {
                        $query->orWhere('id', $movimiento->cuota_id);
                    }
                })
                ->latest('anio')
                ->latest('mes')
                ->get(),
            'categorias' => [
                'Cuotas',
                'Combustible',
                'Letreros',
                'Focos Reflectantes',
                'Libro de Socios',
                'Fotocopias',
                'Materiales',
                'Premio Rifa',
                'Seguridad',
                'Otros',
            ],
        ];
    }
}
