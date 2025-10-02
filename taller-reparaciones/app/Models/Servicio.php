<?php

// ================================================================
// app/Models/Servicio.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    
    protected $fillable = [
        'id_equipo',
        'fecha_recepcion',
        'id_tipo_dano_preliminar',
        'problema_reportado',
        'fecha_entrega',
        'observaciones'
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
        'fecha_entrega' => 'datetime'
    ];

    // Relaciones
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo', 'id_equipo');
    }

    public function tipoDanoPreliminar()
    {
        return $this->belongsTo(TipoDano::class, 'id_tipo_dano_preliminar', 'id_tipo_dano');
    }

    public function tecnicos()
    {
        return $this->belongsToMany(
            Tecnico::class,
            'servicio_tecnicos',
            'id_servicio',
            'id_tecnico'
        )->withPivot('fecha_asignacion', 'rol');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstado::class, 'id_servicio', 'id_servicio')
            ->orderBy('fecha_cambio', 'desc');
    }

    public function estadoActual()
    {
        return $this->hasOne(HistorialEstado::class, 'id_servicio', 'id_servicio')
            ->latestOfMany('fecha_cambio');
    }

    public function repuestos()
    {
        return $this->belongsToMany(
            Repuesto::class,
            'servicio_repuestos',
            'id_servicio',
            'id_repuesto'
        )->withPivot('cantidad', 'costo_total', 'precio_total');
    }

    public function tiempos()
    {
        return $this->hasMany(ServicioTiempo::class, 'id_servicio', 'id_servicio');
    }

    // Calcular costos y precios
    public function getCostoTotalAttribute()
    {
        $costoRepuestos = $this->repuestos->sum('pivot.costo_total');
        $costoManoObra = $this->tiempos->sum(function($tiempo) {
            return $tiempo->horas_estimadas * $tiempo->costo_hora;
        });
        return $costoRepuestos + $costoManoObra;
    }

    public function getPrecioTotalAttribute()
    {
        $precioRepuestos = $this->repuestos->sum('pivot.precio_total');
        $precioManoObra = $this->tiempos->sum(function($tiempo) {
            return $tiempo->horas_estimadas * $tiempo->precio_hora;
        });
        return $precioRepuestos + $precioManoObra;
    }

    public function getGananciaAttribute()
    {
        return $this->precio_total - $this->costo_total;
    }
}

