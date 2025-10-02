<?php
// ================================================================
// app/Http/Controllers/EquipoController.php
// ================================================================
namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Cliente;
use App\Models\TipoEquipo;
use App\Models\Marca;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipo::with(['cliente', 'tipoEquipo', 'marca']);

        if ($request->filled('id_cliente')) {
            $query->where('id_cliente', $request->id_cliente);
        }

        if ($request->filled('id_tipo_equipo')) {
            $query->where('id_tipo_equipo', $request->id_tipo_equipo);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('modelo', 'like', "%{$search}%")
                  ->orWhere('numero_serie', 'like', "%{$search}%");
            });
        }

        $equipos = $query->latest('id_equipo')->paginate(15);
        $clientes = Cliente::orderBy('nombre')->get();
        $tiposEquipo = TipoEquipo::orderBy('nombre')->get();

        return view('equipos.index', compact('equipos', 'clientes', 'tiposEquipo'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $tiposEquipo = TipoEquipo::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        
        return view('equipos.create', compact('clientes', 'tiposEquipo', 'marcas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'id_tipo_equipo' => 'required|exists:tipos_equipo,id_tipo_equipo',
            'id_marca' => 'required|exists:marcas,id_marca',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100'
        ]);

        Equipo::create($validated);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo registrado exitosamente.');
    }

    public function show(Equipo $equipo)
    {
        $equipo->load([
            'cliente',
            'tipoEquipo',
            'marca',
            'servicios.estadoActual',
            'servicios.tecnicos',
            'servicios.historialEstados'
        ]);

        return view('equipos.show', compact('equipo'));
    }

    public function edit(Equipo $equipo)
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $tiposEquipo = TipoEquipo::orderBy('nombre')->get();
        $marcas = Marca::orderBy('nombre')->get();
        
        return view('equipos.edit', compact('equipo', 'clientes', 'tiposEquipo', 'marcas'));
    }

    public function update(Request $request, Equipo $equipo)
    {
        $validated = $request->validate([
            'id_cliente' => 'required|exists:clientes,id_cliente',
            'id_tipo_equipo' => 'required|exists:tipos_equipo,id_tipo_equipo',
            'id_marca' => 'required|exists:marcas,id_marca',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100'
        ]);

        $equipo->update($validated);

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo actualizado exitosamente.');
    }

    public function destroy(Equipo $equipo)
    {
        if ($equipo->servicios()->count() > 0) {
            return redirect()->route('equipos.index')
                ->with('error', 'No se puede eliminar el equipo porque tiene servicios asociados.');
        }

        $equipo->delete();

        return redirect()->route('equipos.index')
            ->with('success', 'Equipo eliminado exitosamente.');
    }
}
