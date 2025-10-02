<?php

// ================================================================
// app/Http/Controllers/TecnicoController.php
// ================================================================
namespace App\Http\Controllers;

use App\Models\Tecnico;
use App\Models\Especialidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TecnicoController extends Controller
{
    public function index(Request $request)
    {
        $query = Tecnico::with('especialidades');

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        $tecnicos = $query->withCount(['servicios' => function($q) {
            $q->whereHas('estadoActual', function($subq) {
                $subq->whereIn('estado', ['Recibido', 'Diagnosticando', 'Reparando', 'Esperando repuestos']);
            });
        }])->orderBy('nombre')->paginate(15);

        return view('tecnicos.index', compact('tecnicos'));
    }

    public function create()
    {
        $especialidades = Especialidad::with('tipoEquipo')->orderBy('nombre')->get();
        
        return view('tecnicos.create', compact('especialidades'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:tecnicos,email',
            'activo' => 'boolean',
            'especialidades' => 'nullable|array',
            'especialidades.*' => 'exists:especialidades,id_especialidad',
            'niveles' => 'nullable|array',
            'niveles.*' => 'in:Aprendiz,Bueno,Experto'
        ]);

        $tecnico = Tecnico::create([
            'nombre' => $validated['nombre'],
            'telefono' => $validated['telefono'] ?? null,
            'email' => $validated['email'] ?? null,
            'activo' => $validated['activo'] ?? true
        ]);

        // Asignar especialidades con niveles
        if (!empty($validated['especialidades'])) {
            $sync_data = [];
            foreach ($validated['especialidades'] as $index => $especialidad_id) {
                $sync_data[$especialidad_id] = [
                    'nivel' => $validated['niveles'][$index] ?? 'Aprendiz'
                ];
            }
            $tecnico->especialidades()->sync($sync_data);
        }

        return redirect()->route('tecnicos.index')
            ->with('success', 'Técnico creado exitosamente.');
    }

    public function show(Tecnico $tecnico)
    {
        $tecnico->load([
            'especialidades.tipoEquipo',
            'servicios' => function($q) {
                $q->with(['equipo.tipoEquipo', 'equipo.cliente', 'estadoActual'])
                  ->latest('fecha_recepcion')
                  ->limit(10);
            }
        ]);

        return view('tecnicos.show', compact('tecnico'));
    }

    public function edit(Tecnico $tecnico)
    {
        $especialidades = Especialidad::with('tipoEquipo')->orderBy('nombre')->get();
        $tecnico->load('especialidades');
        
        return view('tecnicos.edit', compact('tecnico', 'especialidades'));
    }

    public function update(Request $request, Tecnico $tecnico)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:tecnicos,email,' . $tecnico->id_tecnico . ',id_tecnico',
            'activo' => 'boolean',
            'especialidades' => 'nullable|array',
            'especialidades.*' => 'exists:especialidades,id_especialidad',
            'niveles' => 'nullable|array',
            'niveles.*' => 'in:Aprendiz,Bueno,Experto'
        ]);

        $tecnico->update([
            'nombre' => $validated['nombre'],
            'telefono' => $validated['telefono'] ?? null,
            'email' => $validated['email'] ?? null,
            'activo' => $validated['activo'] ?? true
        ]);

        // Actualizar especialidades
        if (isset($validated['especialidades'])) {
            $sync_data = [];
            foreach ($validated['especialidades'] as $index => $especialidad_id) {
                $sync_data[$especialidad_id] = [
                    'nivel' => $validated['niveles'][$index] ?? 'Aprendiz'
                ];
            }
            $tecnico->especialidades()->sync($sync_data);
        } else {
            $tecnico->especialidades()->detach();
        }

        return redirect()->route('tecnicos.index')
            ->with('success', 'Técnico actualizado exitosamente.');
    }

    public function destroy(Tecnico $tecnico)
    {
        // Soft delete: solo desactivar
        $tecnico->update(['activo' => false]);

        return redirect()->route('tecnicos.index')
            ->with('success', 'Técnico desactivado exitosamente.');
    }
}