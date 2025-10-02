<?php

// ================================================================
// app/Models/Equipo.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipos';
    protected $primaryKey = 'id_equipo';
    
    protected $fillable = [
        'id_cliente',
        'id_tipo_equipo',
        'id_marca',
        'modelo',
        'numero_serie'
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_cliente');
    }

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'id_marca', 'id_marca');
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'id_equipo', 'id_equipo');
    }

    // Obtener servicios activos
    public function serviciosActivos()
    {
        return $this->servicios()
            ->whereHas('estadoActual', function($q) {
                $q->whereIn('estado', ['Recibido', 'Diagnosticando', 'Reparando', 'Esperando repuestos']);
            });
    }
}

