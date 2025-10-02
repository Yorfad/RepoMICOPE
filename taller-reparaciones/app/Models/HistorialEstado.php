<?php

// ================================================================
// app/Models/HistorialEstado.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'historial_estados';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;
    
    protected $fillable = [
        'id_servicio',
        'estado',
        'fecha_cambio',
        'id_usuario_responsable',
        'motivo'
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime'
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function responsable()
    {
        return $this->belongsTo(Tecnico::class, 'id_usuario_responsable', 'id_tecnico');
    }
}
