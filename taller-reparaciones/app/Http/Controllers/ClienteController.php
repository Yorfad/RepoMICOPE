<?php
// ================================================================
// app/Http/Controllers/ClienteController.php
// ================================================================
namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Municipio;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::with('municipio');

        // Filtro por origen
        if ($request->filled('origen')) {
            $query->where('origen', $request->origen);
        }

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $clientes = $query->orderBy('visitas_totales', 'desc')->paginate(15);

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $municipios = Municipio::orderBy('nombre')->get();
        $origenes = ['Publicidad', 'Redes sociales', 'Recomendacion', 'Boca a boca', 'Otro'];
        
        return view('clientes.create', compact('municipios', 'origenes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:clientes,email',
            'direccion' => 'nullable|string|max:200',
            'id_municipio' => 'nullable|exists:municipios,id_municipio',
            'origen' => 'required|in:Publicidad,Redes sociales,Recomendacion,Boca a boca,Otro'
        ]);

        Cliente::create($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        $cliente->load(['equipos.tipoEquipo', 'equipos.marca', 'equipos.servicios.estadoActual']);
        
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $municipios = Municipio::orderBy('nombre')->get();
        $origenes = ['Publicidad', 'Redes sociales', 'Recomendacion', 'Boca a boca', 'Otro'];
        
        return view('clientes.edit', compact('cliente', 'municipios', 'origenes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:clientes,email,' . $cliente->id_cliente . ',id_cliente',
            'direccion' => 'nullable|string|max:200',
            'id_municipio' => 'nullable|exists:municipios,id_municipio',
            'origen' => 'required|in:Publicidad,Redes sociales,Recomendacion,Boca a boca,Otro'
        ]);

        $cliente->update($validated);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        // Verificar si tiene equipos asociados
        if ($cliente->equipos()->count() > 0) {
            return redirect()->route('clientes.index')
                ->with('error', 'No se puede eliminar el cliente porque tiene equipos asociados.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
