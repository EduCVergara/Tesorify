<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSocioRequest;
use App\Http\Requests\UpdateSocioRequest;
use App\Models\Socio;

class SocioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $socios = Socio::query()
            ->withCount([
                'cuotas as cuotas_pendientes_count' => fn ($query) => $query->whereIn('estado', ['pendiente', 'parcial']),
            ])
            ->when(request('estado'), fn ($query, $estado) => $query->where('estado', $estado))
            ->when(request('buscar'), function ($query, $buscar) {
                $query->where(function ($query) use ($buscar) {
                    $query->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('codigo_pago', 'like', "%{$buscar}%")
                        ->orWhere('numero_casa', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('socios.index', compact('socios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('socios.create', ['socio' => new Socio(['estado' => 'activo'])]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSocioRequest $request)
    {
        Socio::create($request->validated());

        return redirect()
            ->route('socios.index')
            ->with('status', 'Socio creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Socio $socio)
    {
        $socio->load([
            'cuotas' => fn ($query) => $query->latest('anio')->latest('mes'),
            'movimientos' => fn ($query) => $query->latest('fecha')->latest('id')->limit(10),
        ]);

        return view('socios.show', compact('socio'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Socio $socio)
    {
        return view('socios.edit', compact('socio'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSocioRequest $request, Socio $socio)
    {
        $socio->update($request->validated());

        return redirect()
            ->route('socios.show', $socio)
            ->with('status', 'Socio actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Socio $socio)
    {
        $socio->delete();

        return redirect()
            ->route('socios.index')
            ->with('status', 'Socio eliminado correctamente.');
    }
}
