<?php

// ================================================================
// app/Models/Especialidad.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';
    protected $primaryKey = 'id_especialidad';
    public $timestamps = false;
    
    protected $fillable = ['nombre', 'id_tipo_equipo'];

    public function tipoEquipo()
    {
        return $this->belongsTo(TipoEquipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function tecnicos()
    {
        return $this->belongsToMany(
            Tecnico::class,
            'tecnico_especialidades',
            'id_especialidad',
            'id_tecnico'
        )->withPivot('nivel');
    }
}