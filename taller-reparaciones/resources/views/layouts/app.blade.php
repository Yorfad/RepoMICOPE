@extends('layouts.app')

@section('title', 'Dashboard - Taller de Reparaciones')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="display-5">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h1>
        <p class="text-muted">Vista general del taller</p>
    </div>
</div>

<!-- Estadísticas Generales -->
<div class="row mb-4">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Total Clientes</h6>
                        <h2 class="mb-0">{{ $stats['total_clientes'] ?? 0 }}</h2>
                    </div>
                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Servicios Activos</h6>
                        <h2 class="mb-0">{{ $stats['servicios_activos'] ?? 0 }}</h2>
                    </div>
                    <i class="bi bi-clipboard-check" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Técnicos Activos</h6>
                        <h2 class="mb-0">{{ $stats['tecnicos_activos'] ?? 0 }}</h2>
                    </div>
                    <i class="bi bi-person-badge" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Repuestos Bajo Stock</h6>
                        <h2 class="mb-0">{{ $stats['repuestos_bajo_stock'] ?? 0 }}</h2>
                    </div>
                    <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Servicios por Estado y Carga de Técnicos -->
<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Servicios por Estado</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th class="text-end">Cantidad</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($serviciosPorEstado as $estado)
                            <tr>
                                <td>
                                    @php
                                        $badge = match($estado->estado_actual) {
                                            'Recibido' => 'bg-secondary',
                                            'Diagnosticando' => 'bg-info text-dark',
                                            'Reparando' => 'bg-primary',
                                            'Esperando repuestos' => 'bg-warning text-dark',
                                            'Finalizado' => 'bg-success',
                                            'Entregado' => 'bg-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $estado->estado_actual }}</span>
                                </td>
                                <td class="text-end"><strong>{{ $estado->total }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No hay servicios registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Carga de Técnicos</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th class="text-end">Servicios Activos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cargaTecnicos as $tecnico)
                            <tr>
                                <td>
                                    <i class="bi bi-person-circle"></i> {{ $tecnico->nombre }}
                                </td>
                                <td class="text-end">
                                    @if($tecnico->servicios_count > 5)
                                        <span class="badge bg-danger">{{ $tecnico->servicios_count }}</span>
                                    @elseif($tecnico->servicios_count > 2)
                                        <span class="badge bg-warning text-dark">{{ $tecnico->servicios_count }}</span>
                                    @else
                                        <span class="badge bg-success">{{ $tecnico->servicios_count }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No hay técnicos activos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Servicios Recientes -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Servicios Recientes</h5>
                <a href="{{ route('servicios.index') }}" class="btn btn-sm btn-outline-primary">
                    Ver todos
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Equipo</th>
                                <th>Fecha Recepción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviciosRecientes as $servicio)
                                <tr>
                                    <td>#{{ $servicio->id_servicio }}</td>
                                    <td>{{ $servicio->equipo->cliente->nombre ?? 'N/A' }}</td>
                                    <td>
                                        {{ $servicio->equipo->tipoEquipo->nombre ?? '' }}
                                        {{ $servicio->equipo->marca->nombre ?? '' }}
                                    </td>
                                    <td>{{ $servicio->fecha_recepcion ? $servicio->fecha_recepcion->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        @if($servicio->estadoActual)
                                            @php
                                                $badgeClass = match($servicio->estadoActual->estado) {
                                                    'Recibido' => 'bg-secondary',
                                                    'Diagnosticando' => 'bg-info text-dark',
                                                    'Reparando' => 'bg-primary',
                                                    'Esperando repuestos' => 'bg-warning text-dark',
                                                    'Finalizado' => 'bg-success',
                                                    'Entregado' => 'bg-dark',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $servicio->estadoActual->estado }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark">Sin estado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('servicios.show', $servicio) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No hay servicios recientes</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Repuestos Bajo Stock -->
@if($repuestosBajoStock->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle-fill"></i> Repuestos con Bajo Stock
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Repuesto</th>
                                <th class="text-end">Stock Actual</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($repuestosBajoStock as $repuesto)
                                <tr>
                                    <td>{{ $repuesto->nombre }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-danger">{{ $repuesto->cantidad }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('repuestos.edit', $repuesto) }}" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil"></i> Actualizar
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

