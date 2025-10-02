<?php
// ================================================================
// app/Http/Controllers/RepuestoController.php
// ================================================================
namespace App\Http\Controllers;

use App\Models\Repuesto;
use Illuminate\Http\Request;

class RepuestoController extends Controller
{
    public function index(Request $request)
    {
        $query = Repuesto::query();

        if ($request->filled('activo')) {
            $query->where('activo', $request->activo);
        }

        if ($request->filled('bajo_stock')) {
            $query->where('cantidad', '<=', 5);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        $repuestos = $query->orderBy('nombre')->paginate(15);

        return view('repuestos.index', compact('repuestos'));
    }

    public function create()
    {
        return view('repuestos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'cantidad' => 'required|integer|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'activo' => 'boolean'
        ]);

        Repuesto::create($validated);

        return redirect()->route('repuestos.index')
            ->with('success', 'Repuesto creado exitosamente.');
    }

    public function show(Repuesto $repuesto)
    {
        $repuesto->load(['servicios' => function($q) {
            $q->with(['equipo.cliente', 'estadoActual'])
              ->latest('fecha_recepcion')
              ->limit(10);
        }]);

        return view('repuestos.show', compact('repuesto'));
    }

    public function edit(Repuesto $repuesto)
    {
        return view('repuestos.edit', compact('repuesto'));
    }

    public function update(Request $request, Repuesto $repuesto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'descripcion' => 'nullable|string',
            'cantidad' => 'required|integer|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'activo' => 'boolean'
        ]);

        $repuesto->update($validated);

        return redirect()->route('repuestos.index')
            ->with('success', 'Repuesto actualizado exitosamente.');
    }

    public function destroy(Repuesto $repuesto)
    {
        // Soft delete: desactivar
        $repuesto->update(['activo' => false]);

        return redirect()->route('repuestos.index')
            ->with('success', 'Repuesto desactivado exitosamente.');
    }
}
