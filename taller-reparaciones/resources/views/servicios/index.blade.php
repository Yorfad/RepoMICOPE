{{-- ================================================================
     resources/views/servicios/index.blade.php
     ================================================================ --}}
@extends('layouts.app')

@section('title', 'Servicios')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-clipboard-check"></i> Servicios</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('servicios.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Servicio
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('servicios.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>
                            {{ $estado }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="id_cliente" class="form-select">
                    <option value="">Todos los clientes</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id_cliente }}" 
                                {{ request('id_cliente') == $cliente->id_cliente ? 'selected' : '' }}>
                            {{ $cliente->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="id_tecnico" class="form-select">
                    <option value="">Todos los técnicos</option>
                    @foreach($tecnicos as $tecnico)
                        <option value="{{ $tecnico->id_tecnico }}" 
                                {{ request('id_tecnico') == $tecnico->id_tecnico ? 'selected' : '' }}>
                            {{ $tecnico->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Servicios -->
<div class="card">
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
                        <th>Técnicos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($servicios as $servicio)
                        <tr>
                            <td><strong>#{{ $servicio->id_servicio }}</strong></td>
                            <td>{{ $servicio->equipo->cliente->nombre }}</td>
                            <td>
                                {{ $servicio->equipo->tipoEquipo->nombre }}
                                <br><small class="text-muted">
                                    {{ $servicio->equipo->marca->nombre }} {{ $servicio->equipo->modelo }}
                                </small>
                            </td>
                            <td>{{ $servicio->fecha_recepcion->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $badge = match($servicio->estadoActual->estado) {
                                        'Recibido' => 'bg-secondary',
                                        'Diagnosticando' => 'bg-info',
                                        'Reparando' => 'bg-primary',
                                        'Esperando repuestos' => 'bg-warning text-dark',
                                        'Finalizado' => 'bg-success',
                                        'Entregado' => 'bg-dark',
                                        'Cancelado' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">
                                    {{ $servicio->estadoActual->estado }}
                                </span>
                            </td>
                            <td>
                                @forelse($servicio->tecnicos as $tecnico)
                                    <small class="badge bg-light text-dark">
                                        {{ $tecnico->nombre }}
                                    </small>
                                @empty
                                    <small class="text-muted">Sin asignar</small>
                                @endforelse
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('servicios.show', $servicio) }}" 
                                       class="btn btn-outline-info" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('servicios.edit', $servicio) }}" 
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay servicios registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $servicios->links() }}
        </div>
    </div>
</div>
@endsection

{{-- ================================================================
     resources/views/servicios/create.blade.php
     ================================================================ --}}
@extends('layouts.app')

@section('title', 'Nuevo Servicio')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-clipboard-plus"></i> Registrar Nuevo Servicio</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('servicios.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_equipo" class="form-label">
                                Equipo del Cliente <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('id_equipo') is-invalid @enderror" 
                                    id="id_equipo" name="id_equipo" required>
                                <option value="">Seleccione un equipo</option>
                                @foreach($equipos as $equipo)
                                    <option value="{{ $equipo->id_equipo }}" 
                                            {{ old('id_equipo') == $equipo->id_equipo ? 'selected' : '' }}>
                                        {{ $equipo->cliente->nombre }} - 
                                        {{ $equipo->tipoEquipo->nombre }} 
                                        {{ $equipo->marca->nombre }} 
                                        {{ $equipo->modelo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_equipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Si el cliente no tiene equipos, primero debe registrar uno.
                            </small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="id_tipo_dano_preliminar" class="form-label">
                                Tipo de Daño Reportado
                            </label>
                            <select class="form-select @error('id_tipo_dano_preliminar') is-invalid @enderror" 
                                    id="id_tipo_dano_preliminar" name="id_tipo_dano_preliminar">
                                <option value="">Seleccione el tipo de daño</option>
                                @foreach($tiposDano->groupBy('tipoEquipo.nombre') as $tipoEquipo => $danos)
                                    <optgroup label="{{ $tipoEquipo }}">
                                        @foreach($danos as $dano)
                                            <option value="{{ $dano->id_tipo_dano }}" 
                                                    {{ old('id_tipo_dano_preliminar') == $dano->id_tipo_dano ? 'selected' : '' }}>
                                                {{ $dano->nombre }} 
                                                (≈{{ $dano->tiempo_estimado_horas }}h)
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('id_tipo_dano_preliminar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="problema_reportado" class="form-label">
                            Problema Reportado por el Cliente <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('problema_reportado') is-invalid @enderror" 
                                  id="problema_reportado" name="problema_reportado" 
                                  rows="4" required>{{ old('problema_reportado') }}</textarea>
                        @error('problema_reportado')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Describa detalladamente el problema que reporta el cliente
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones Adicionales</label>
                        <textarea class="form-control @error('observaciones') is-invalid @enderror" 
                                  id="observaciones" name="observaciones" 
                                  rows="3">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Nota:</strong> Al registrar el servicio se incrementará automáticamente 
                        el contador de visitas del cliente y se creará el estado inicial "Recibido".
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('servicios.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Registrar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ================================================================
     resources/views/servicios/show.blade.php
     ================================================================ --}}
@extends('layouts.app')

@section('title', 'Detalle del Servicio')

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <h1><i class="bi bi-clipboard-check"></i> Servicio #{{ $servicio->id_servicio }}</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('servicios.edit', $servicio) }}" class="btn btn-primary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('servicios.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Equipo y Cliente -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-laptop"></i> Información del Equipo</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Cliente:</th>
                        <td>
                            <strong>{{ $servicio->equipo->cliente->nombre }}</strong>
                            <br><small>
                                <i class="bi bi-telephone"></i> {{ $servicio->equipo->cliente->telefono }}
                            </small>
                        </td>
                    </tr>
                    <tr>
                        <th>Tipo de Equipo:</th>
                        <td>{{ $servicio->equipo->tipoEquipo->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Marca:</th>
                        <td>{{ $servicio->equipo->marca->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Modelo:</th>
                        <td>{{ $servicio->equipo->modelo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Número de Serie:</th>
                        <td>{{ $servicio->equipo->numero_serie ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Estado Actual y Fechas -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Estado del Servicio</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Estado Actual:</th>
                        <td>
                            @php
                                $estadoActual = $servicio->estadoActual;
                                $badge = match($estadoActual->estado) {
                                    'Recibido' => 'bg-secondary',
                                    'Diagnosticando' => 'bg-info',
                                    'Reparando' => 'bg-primary',
                                    'Esperando repuestos' => 'bg-warning text-dark',
                                    'Finalizado' => 'bg-success',
                                    'Entregado' => 'bg-dark',
                                    'Cancelado' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $badge }} fs-6">
                                {{ $estadoActual->estado }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Fecha Recepción:</th>
                        <td>{{ $servicio->fecha_recepcion->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Fecha Entrega:</th>
                        <td>
                            @if($servicio->fecha_entrega)
                                {{ $servicio->fecha_entrega->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Última Actualización:</th>
                        <td>{{ $estadoActual->fecha_cambio->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Daño Reportado:</th>
                        <td>
                            @if($servicio->tipoDanoPreliminar)
                                <span class="badge bg-warning text-dark">
                                    {{ $servicio->tipoDanoPreliminar->nombre }}
                                </span>
                            @else
                                <span class="text-muted">No especificado</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Problema Reportado -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-chat-square-text"></i> Problema Reportado</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $servicio->problema_reportado }}</p>
                @if($servicio->observaciones)
                    <hr>
                    <p class="mb-0 text-muted">
                        <strong>Observaciones:</strong> {{ $servicio->observaciones }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Técnicos Asignados -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-badge"></i> Técnicos Asignados</h5>
                <button type="button" class="btn btn-sm btn-primary" 
                        data-bs-toggle="modal" data-bs-target="#asignarTecnicoModal">
                    <i class="bi bi-plus"></i> Asignar
                </button>
            </div>
            <div class="card-body">
                @forelse($servicio->tecnicos as $tecnico)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <i class="bi bi-person-circle"></i> 
                            <strong>{{ $tecnico->nombre }}</strong>
                            <br><small class="text-muted">
                                Rol: {{ $tecnico->pivot->rol }} | 
                                Asignado: {{ \Carbon\Carbon::parse($tecnico->pivot->fecha_asignacion)->format('d/m/Y') }}
                            </small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No hay técnicos asignados</p>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Costos y Precios -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-currency-dollar"></i> Resumen Financiero</h5>
            </div>
            <div class="card-body">
                @php
                    $costoRepuestos = $servicio->repuestos->sum('pivot.costo_total');
                    $precioRepuestos = $servicio->repuestos->sum('pivot.precio_total');
                    $costoManoObra = $servicio->tiempos->sum(function($t) {
                        return $t->horas_estimadas * $t->costo_hora;
                    });
                    $precioManoObra = $servicio->tiempos->sum(function($t) {
                        return $t->horas_estimadas * $t->precio_hora;
                    });
                    $costoTotal = $costoRepuestos + $costoManoObra;
                    $precioTotal = $precioRepuestos + $precioManoObra;
                    $ganancia = $precioTotal - $costoTotal;
                @endphp
                
                <table class="table table-sm">
                    <tr>
                        <th>Costo Repuestos:</th>
                        <td class="text-end">Q{{ number_format($costoRepuestos, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Costo Mano de Obra:</th>
                        <td class="text-end">Q{{ number_format($costoManoObra, 2) }}</td>
                    </tr>
                    <tr class="table-secondary">
                        <th>Costo Total:</th>
                        <th class="text-end">Q{{ number_format($costoTotal, 2) }}</th>
                    </tr>
                    <tr>
                        <th>Precio Repuestos:</th>
                        <td class="text-end">Q{{ number_format($precioRepuestos, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Precio Mano de Obra:</th>
                        <td class="text-end">Q{{ number_format($precioManoObra, 2) }}</td>
                    </tr>
                    <tr class="table-primary">
                        <th>Precio Total:</th>
                        <th class="text-end">Q{{ number_format($precioTotal, 2) }}</th>
                    </tr>
                    <tr class="table-success">
                        <th>Ganancia Estimada:</th>
                        <th class="text-end">Q{{ number_format($ganancia, 2) }}</th>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Estados -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Historial de Estados</h5>
                <button type="button" class="btn btn-sm btn-warning" 
                        data-bs-toggle="modal" data-bs-target="#cambiarEstadoModal">
                    <i class="bi bi-arrow-repeat"></i> Cambiar Estado
                </button>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($servicio->historialEstados as $historial)
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <div>
                                    @php
                                        $badge = match($historial->estado) {
                                            'Recibido' => 'bg-secondary',
                                            'Diagnosticando' => 'bg-info',
                                            'Reparando' => 'bg-primary',
                                            'Esperando repuestos' => 'bg-warning text-dark',
                                            'Finalizado' => 'bg-success',
                                            'Entregado' => 'bg-dark',
                                            'Cancelado' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $historial->estado }}</span>
                                    @if($historial->responsable)
                                        <small class="text-muted">
                                            por {{ $historial->responsable->nombre }}
                                        </small>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $historial->fecha_cambio->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            @if($historial->motivo)
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-chat-left-text"></i> {{ $historial->motivo }}
                                </small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asignar Técnico -->
<div class="modal fade" id="asignarTecnicoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('servicios.asignar-tecnico', $servicio) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Técnico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_tecnico" class="form-label">Técnico</label>
                        <select class="form-select" id="id_tecnico" name="id_tecnico" required>
                            <option value="">Seleccione un técnico</option>
                            @foreach(\App\Models\Tecnico::activos()->get() as $tec)
                                <option value="{{ $tec->id_tecnico }}">{{ $tec->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rol" class="form-label">Rol</label>
                        <select class="form-select" id="rol" name="rol" required>
                            <option value="Principal">Principal</option>
                            <option value="Asistente">Asistente</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cambiar Estado -->
<div class="modal fade" id="cambiarEstadoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('servicios.cambiar-estado', $servicio) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar Estado del Servicio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="estado" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Recibido">Recibido</option>
                            <option value="Diagnosticando">Diagnosticando</option>
                            <option value="Esperando repuestos">Esperando repuestos</option>
                            <option value="Reparando">Reparando</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Entregado">Entregado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo del Cambio</label>
                        <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Cambiar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection