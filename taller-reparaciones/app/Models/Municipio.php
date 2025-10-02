<?php

// ================================================================
// app/Models/Municipio.php
// ================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    protected $table = 'municipios';
    protected $primaryKey = 'id_municipio';
    public $timestamps = false;
    
    protected $fillable = ['nombre', 'departamento'];

    public function clientes()
    {
        return $this->hasMany(Cliente::class, 'id_municipio', 'id_municipio');
    }
}