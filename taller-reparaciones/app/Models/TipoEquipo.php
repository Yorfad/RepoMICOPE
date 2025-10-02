<?php

// ================================================================
// app/Models/TipoEquipo.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoEquipo extends Model
{
    use HasFactory;

    protected $table = 'tipos_equipo';
    protected $primaryKey = 'id_tipo_equipo';
    public $timestamps = false;
    
    protected $fillable = ['nombre', 'descripcion'];

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function especialidades()
    {
        return $this->hasMany(Especialidad::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }

    public function tiposDano()
    {
        return $this->hasMany(TipoDano::class, 'id_tipo_equipo', 'id_tipo_equipo');
    }
}
