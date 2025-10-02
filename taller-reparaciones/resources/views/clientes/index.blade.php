{{-- ================================================================
     resources/views/clientes/index.blade.php
     ================================================================ --}}
@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-people"></i> Clientes</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Cliente
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('clientes.index') }}" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" 
                       placeholder="Buscar por nombre, teléfono o email..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="origen" class="form-select">
                    <option value="">Todos los orígenes</option>
                    <option value="Publicidad" {{ request('origen') == 'Publicidad' ? 'selected' : '' }}>
                        Publicidad
                    </option>
                    <option value="Redes sociales" {{ request('origen') == 'Redes sociales' ? 'selected' : '' }}>
                        Redes sociales
                    </option>
                    <option value="Recomendacion" {{ request('origen') == 'Recomendacion' ? 'selected' : '' }}>
                        Recomendación
                    </option>
                    <option value="Boca a boca" {{ request('origen') == 'Boca a boca' ? 'selected' : '' }}>
                        Boca a boca
                    </option>
                    <option value="Otro" {{ request('origen') == 'Otro' ? 'selected' : '' }}>
                        Otro
                    </option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            <div class="col-md-3">
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Clientes -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Origen</th>
                        <th>Visitas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id_cliente }}</td>
                            <td>
                                <strong>{{ $cliente->nombre }}</strong>
                                @if($cliente->municipio)
                                    <br><small class="text-muted">
                                        <i class="bi bi-geo-alt"></i> {{ $cliente->municipio->nombre }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $cliente->telefono }}</td>
                            <td>{{ $cliente->email }}</td>
                            <td>
                                <span class="badge bg-info">{{ $cliente->origen }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $cliente->visitas_totales }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('clientes.show', $cliente) }}" 
                                       class="btn btn-outline-info" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('clientes.edit', $cliente) }}" 
                                       class="btn btn-outline-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('clientes.destroy', $cliente) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('¿Está seguro de eliminar este cliente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay clientes registrados</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Paginación -->
        <div class="mt-3">
            {{ $clientes->links() }}
        </div>
    </div>
</div>
@endsection

{{-- ================================================================
     resources/views/clientes/create.blade.php
     ================================================================ --}}
@extends('layouts.app')

@section('title', 'Nuevo Cliente')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> Nuevo Cliente</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" name="telefono" value="{{ old('telefono') }}">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                               id="direccion" name="direccion" value="{{ old('direccion') }}">
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_municipio" class="form-label">Municipio</label>
                            <select class="form-select @error('id_municipio') is-invalid @enderror" 
                                    id="id_municipio" name="id_municipio">
                                <option value="">Seleccione un municipio</option>
                                @foreach($municipios as $municipio)
                                    <option value="{{ $municipio->id_municipio }}" 
                                            {{ old('id_municipio') == $municipio->id_municipio ? 'selected' : '' }}>
                                        {{ $municipio->nombre }}, {{ $municipio->departamento }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_municipio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="origen" class="form-label">¿Cómo nos conoció? <span class="text-danger">*</span></label>
                            <select class="form-select @error('origen') is-invalid @enderror" 
                                    id="origen" name="origen" required>
                                @foreach($origenes as $origen)
                                    <option value="{{ $origen }}" 
                                            {{ old('origen') == $origen ? 'selected' : '' }}>
                                        {{ $origen }}
                                    </option>
                                @endforeach
                            </select>
                            @error('origen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- ================================================================
     resources/views/clientes/edit.blade.php
     ================================================================ --}}
@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-pencil"></i> Editar Cliente</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre') is-invalid @enderror" 
                               id="nombre" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required>
                        @error('nombre')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('telefono') is-invalid @enderror" 
                                   id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $cliente->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control @error('direccion') is-invalid @enderror" 
                               id="direccion" name="direccion" value="{{ old('direccion', $cliente->direccion) }}">
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_municipio" class="form-label">Municipio</label>
                            <select class="form-select @error('id_municipio') is-invalid @enderror" 
                                    id="id_municipio" name="id_municipio">
                                <option value="">Seleccione un municipio</option>
                                @foreach($municipios as $municipio)
                                    <option value="{{ $municipio->id_municipio }}" 
                                            {{ old('id_municipio', $cliente->id_municipio) == $municipio->id_municipio ? 'selected' : '' }}>
                                        {{ $municipio->nombre }}, {{ $municipio->departamento }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_municipio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="origen" class="form-label">Origen <span class="text-danger">*</span></label>
                            <select class="form-select @error('origen') is-invalid @enderror" 
                                    id="origen" name="origen" required>
                                @foreach($origenes as $origen)
                                    <option value="{{ $origen }}" 
                                            {{ old('origen', $cliente->origen) == $origen ? 'selected' : '' }}>
                                        {{ $origen }}
                                    </option>
                                @endforeach
                            </select>
                            @error('origen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Actualizar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection