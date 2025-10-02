<?php
// ================================================================
// app/Http/Controllers/MarcaController.php
// ================================================================
namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $marcas = Marca::withCount('equipos')->orderBy('nombre')->paginate(20);
        return view('marcas.index', compact('marcas'));
    }

    public function create()
    {
        return view('marcas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre'
        ]);

        Marca::create($validated);

        return redirect()->route('marcas.index')
            ->with('success', 'Marca creada exitosamente.');
    }

    public function edit(Marca $marca)
    {
        return view('marcas.edit', compact('marca'));
    }

    public function update(Request $request, Marca $marca)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre,' . $marca->id_marca . ',id_marca'
        ]);

        $marca->update($validated);

        return redirect()->route('marcas.index')
            ->with('success', 'Marca actualizada exitosamente.');
    }

    public function destroy(Marca $marca)
    {
        if ($marca->equipos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la marca porque tiene equipos asociados.');
        }

        $marca->delete();

        return redirect()->route('marcas.index')
            ->with('success', 'Marca eliminada exitosamente.');
    }
}
