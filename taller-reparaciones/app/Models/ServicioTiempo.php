<?php

// ================================================================
// app/Models/ServicioTiempo.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioTiempo extends Model
{
    use HasFactory;

    protected $table = 'servicio_tiempos';
    protected $primaryKey = 'id_servicio_tiempo';
    public $timestamps = false;
    
    protected $fillable = [
        'id_servicio',
        'id_tecnico',
        'horas_estimadas',
        'horas_reales',
        'costo_hora',
        'precio_hora'
    ];

    protected $casts = [
        'horas_estimadas' => 'decimal:2',
        'horas_reales' => 'decimal:2',
        'costo_hora' => 'decimal:2',
        'precio_hora' => 'decimal:2'
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }

    public function tecnico()
    {
        return $this->belongsTo(Tecnico::class, 'id_tecnico', 'id_tecnico');
    }
}