<?php

// ================================================================
// app/Models/TipoDano.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDano extends Model
{
    use HasFactory;

    protected $table = 'tipos_dano';
    protected $primaryKey = 'id_tipo_dano';
    public $timestamps = false;
    
    protected $fillable = [
        'nombre',
        'id_tipo_equipo',
        'tiempo_estimado_horas',
        'descripcion'
    ];

    protected $casts = [
        'tiempo_estimado_horas' => 'decimal:2'
    ];

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'id_tipo_dano_preliminar', 'id_tipo_dano');
    }
}