<?php
// ================================================================
// app/Http/Controllers/DashboardController.php
// ================================================================
namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Tecnico;
use App\Models\Cliente;
use App\Models\Repuesto;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Servicios por estado
        $serviciosPorEstado = DB::table('historial_estados as h1')
            ->select('h1.estado', DB::raw('COUNT(DISTINCT h1.id_servicio) as total'))
            ->whereRaw('h1.fecha_cambio = (
                SELECT MAX(h2.fecha_cambio) 
                FROM historial_estados h2 
                WHERE h2.id_servicio = h1.id_servicio
            )')
            ->groupBy('h1.estado')
            ->get();

        // Técnicos y su carga
        $cargaTecnicos = Tecnico::where('activo', true)
            ->withCount(['servicios' => function($q) {
                $q->whereHas('estadoActual', function($subq) {
                    $subq->whereIn('estado', ['Recibido', 'Diagnosticando', 'Reparando', 'Esperando repuestos']);
                });
            }])
            ->orderBy('servicios_count', 'desc')
            ->limit(10)
            ->get();

        // Repuestos con bajo stock
        $repuestosBajoStock = Repuesto::where('activo', true)
            ->where('cantidad', '<=', 5)
            ->limit(10)
            ->get();

        // Estadísticas generales
        $stats = [
            'total_clientes' => Cliente::count(),
            'servicios_activos' => Servicio::whereHas('estadoActual', function($q) {
                $q->whereIn('estado', ['Recibido', 'Diagnosticando', 'Reparando', 'Esperando repuestos']);
            })->count(),
            'tecnicos_activos' => Tecnico::where('activo', true)->count(),
            'repuestos_bajo_stock' => Repuesto::where('activo', true)
                ->where('cantidad', '<=', 5)
                ->count()
        ];

        // Servicios recientes
        $serviciosRecientes = Servicio::with([
            'equipo.cliente',
            'equipo.tipoEquipo',
            'estadoActual'
        ])->latest('fecha_recepcion')->limit(5)->get();

        return view('dashboard', compact(
            'serviciosPorEstado',
            'cargaTecnicos',
            'repuestosBajoStock',
            'stats',
            'serviciosRecientes'
        ));
    }
}