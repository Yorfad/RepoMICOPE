<?php
// ================================================================
// app/Http/Controllers/ServicioController.php
// ================================================================
namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Equipo;
use App\Models\Cliente;
use App\Models\TipoDano;
use App\Models\Tecnico;
use App\Models\HistorialEstado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $query = Servicio::with([
            'equipo.cliente',
            'equipo.tipoEquipo',
            'equipo.marca',
            'estadoActual',
            'tecnicos'
        ]);

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->whereHas('estadoActual', function($q) use ($request) {
                $q->where('estado', $request->estado);
            });
        }

        // Filtro por cliente
        if ($request->filled('id_cliente')) {
            $query->whereHas('equipo', function($q) use ($request) {
                $q->where('id_cliente', $request->id_cliente);
            });
        }

        // Filtro por técnico
        if ($request->filled('id_tecnico')) {
            $query->whereHas('tecnicos', function($q) use ($request) {
                $q->where('tecnicos.id_tecnico', $request->id_tecnico);
            });
        }

        $servicios = $query->latest('fecha_recepcion')->paginate(15);
        
        $clientes = Cliente::orderBy('nombre')->get();
        $tecnicos = Tecnico::activos()->orderBy('nombre')->get();
        $estados = ['Recibido', 'Diagnosticando', 'Esperando repuestos', 'Reparando', 'Finalizado', 'Entregado', 'Cancelado'];

        return view('servicios.index', compact('servicios', 'clientes', 'tecnicos', 'estados'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $equipos = Equipo::with(['cliente', 'tipoEquipo', 'marca'])->get();
        $tiposDano = TipoDano::with('tipoEquipo')->orderBy('nombre')->get();
        
        return view('servicios.create', compact('clientes', 'equipos', 'tiposDano'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_equipo' => 'required|exists:equipos,id_equipo',
            'problema_reportado' => 'required|string',
            'id_tipo_dano_preliminar' => 'nullable|exists:tipos_dano,id_tipo_dano',
            'observaciones' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            // Crear servicio
            $servicio = Servicio::create([
                'id_equipo' => $validated['id_equipo'],
                'fecha_recepcion' => now(),
                'problema_reportado' => $validated['problema_reportado'],
                'id_tipo_dano_preliminar' => $validated['id_tipo_dano_preliminar'] ?? null,
                'observaciones' => $validated['observaciones'] ?? null
            ]);

            // Crear estado inicial
            HistorialEstado::create([
                'id_servicio' => $servicio->id_servicio,
                'estado' => 'Recibido',
                'fecha_cambio' => now(),
                'motivo' => 'Equipo recibido del cliente'
            ]);

            // Incrementar visitas del cliente
            $equipo = Equipo::find($validated['id_equipo']);
            $equipo->cliente->increment('visitas_totales');

            DB::commit();

            return redirect()->route('servicios.show', $servicio)
                ->with('success', 'Servicio registrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Error al crear el servicio: ' . $e->getMessage());
        }
    }

    public function show(Servicio $servicio)
    {
        $servicio->load([
            'equipo.cliente',
            'equipo.tipoEquipo',
            'equipo.marca',
            'tipoDanoPreliminar',
            'tecnicos',
            'historialEstados.responsable',
            'repuestos',
            'tiempos.tecnico'
        ]);

        return view('servicios.show', compact('servicio'));
    }

    public function edit(Servicio $servicio)
    {
        $equipos = Equipo::with(['cliente', 'tipoEquipo', 'marca'])->get();
        $tiposDano = TipoDano::with('tipoEquipo')->orderBy('nombre')->get();
        $tecnicos = Tecnico::activos()->orderBy('nombre')->get();
        
        $servicio->load(['tecnicos', 'estadoActual']);

        return view('servicios.edit', compact('servicio', 'equipos', 'tiposDano', 'tecnicos'));
    }

    public function update(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'id_equipo' => 'required|exists:equipos,id_equipo',
            'problema_reportado' => 'required|string',
            'id_tipo_dano_preliminar' => 'nullable|exists:tipos_dano,id_tipo_dano',
            'observaciones' => 'nullable|string',
            'fecha_entrega' => 'nullable|date'
        ]);

        $servicio->update($validated);

        return redirect()->route('servicios.show', $servicio)
            ->with('success', 'Servicio actualizado exitosamente.');
    }

    public function destroy(Servicio $servicio)
    {
        // Verificar si se puede eliminar
        $estadoActual = $servicio->estadoActual;
        
        if ($estadoActual && in_array($estadoActual->estado, ['Reparando', 'Finalizado', 'Entregado'])) {
            return redirect()->route('servicios.index')
                ->with('error', 'No se puede eliminar un servicio en estado: ' . $estadoActual->estado);
        }

        DB::beginTransaction();
        try {
            // Eliminar relaciones
            $servicio->historialEstados()->delete();
            $servicio->tecnicos()->detach();
            $servicio->repuestos()->detach();
            $servicio->tiempos()->delete();
            
            $servicio->delete();
            
            DB::commit();

            return redirect()->route('servicios.index')
                ->with('success', 'Servicio eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el servicio: ' . $e->getMessage());
        }
    }

    /**
     * Asignar técnico a un servicio
     */
    public function asignarTecnico(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'id_tecnico' => 'required|exists:tecnicos,id_tecnico',
            'rol' => 'required|in:Principal,Asistente'
        ]);

        // Verificar si ya está asignado
        if ($servicio->tecnicos()->where('id_tecnico', $validated['id_tecnico'])->exists()) {
            return back()->with('error', 'El técnico ya está asignado a este servicio.');
        }

        $servicio->tecnicos()->attach($validated['id_tecnico'], [
            'fecha_asignacion' => now(),
            'rol' => $validated['rol']
        ]);

        // Crear historial
        HistorialEstado::create([
            'id_servicio' => $servicio->id_servicio,
            'estado' => 'Diagnosticando',
            'fecha_cambio' => now(),
            'id_usuario_responsable' => $validated['id_tecnico'],
            'motivo' => 'Técnico asignado al servicio'
        ]);

        return back()->with('success', 'Técnico asignado exitosamente.');
    }

    /**
     * Cambiar estado del servicio
     */
    public function cambiarEstado(Request $request, Servicio $servicio)
    {
        $validated = $request->validate([
            'estado' => 'required|in:Recibido,Diagnosticando,Esperando repuestos,Reparando,Finalizado,Entregado,Cancelado',
            'id_tecnico' => 'nullable|exists:tecnicos,id_tecnico',
            'motivo' => 'required|string'
        ]);

        HistorialEstado::create([
            'id_servicio' => $servicio->id_servicio,
            'estado' => $validated['estado'],
            'fecha_cambio' => now(),
            'id_usuario_responsable' => $validated['id_tecnico'] ?? null,
            'motivo' => $validated['motivo']
        ]);

        // Si se entrega, actualizar fecha
        if ($validated['estado'] === 'Entregado') {
            $servicio->update(['fecha_entrega' => now()]);
        }

        return back()->with('success', 'Estado actualizado exitosamente.');
    }
}
