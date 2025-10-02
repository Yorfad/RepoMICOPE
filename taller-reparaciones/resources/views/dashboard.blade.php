<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Taller de Reparaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="bi bi-tools"></i> Taller Reparaciones
            </a>
        </div>
    </nav>

    <div class="container-fluid py-4">
        @if(isset($error))
            <div class="alert alert-danger">
                <strong>Error:</strong> {{ $error }}
                <br><small>Verifica que la base de datos esté configurada correctamente.</small>
            </div>
        @endif

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
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Servicios Activos</h6>
                                <h2 class="mb-0">{{ $stats['servicios_activos'] }}</h2>
                            </div>
                            <i class="bi bi-clipboard-check" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Clientes</h6>
                                <h2 class="mb-0">{{ $stats['total_clientes'] }}</h2>
                            </div>
                            <i class="bi bi-people" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Técnicos Activos</h6>
                                <h2 class="mb-0">{{ $stats['tecnicos_activos'] }}</h2>
                            </div>
                            <i class="bi bi-person-badge" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Repuestos Bajo Stock</h6>
                                <h2 class="mb-0">{{ $stats['repuestos_bajo_stock'] }}</h2>
                            </div>
                            <i class="bi bi-exclamation-triangle" style="font-size: 3rem; opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Servicios por Estado -->
        <div class="row mb-4">
            <div class="col-md-6">
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
                                            <span class="badge bg-primary">{{ $estado->estado }}</span>
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
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Carga de Técnicos</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Técnico</th>
                                    <th class="text-end">Servicios</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cargaTecnicos as $tecnico)
                                    <tr>
                                        <td>
                                            <i class="bi bi-person-circle"></i> {{ $tecnico->nombre }}
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-success">{{ $tecnico->servicios_count }}</span>
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
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Servicios Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Equipo</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($serviciosRecientes as $servicio)
                                        <tr>
                                            <td>#{{ $servicio->id_servicio }}</td>
                                            <td>{{ $servicio->cliente_nombre }}</td>
                                            <td>{{ $servicio->tipo_equipo }}</td>
                                            <td>{{ $servicio->fecha_recepcion }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $servicio->estado ?? 'Recibido' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay servicios recientes</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(count($repuestosBajoStock) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="bi bi-exclamation-triangle"></i> Repuestos con Bajo Stock
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Repuesto</th>
                                        <th class="text-end">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($repuestosBajoStock as $repuesto)
                                        <tr>
                                            <td>{{ $repuesto->nombre }}</td>
                                            <td class="text-end">
                                                <span class="badge bg-danger">{{ $repuesto->cantidad }}</span>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>