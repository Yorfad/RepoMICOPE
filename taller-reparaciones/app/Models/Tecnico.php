<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tecnico extends Model
{
    use HasFactory;

    protected $table = 'tecnicos';
    protected $primaryKey = 'id_tecnico';
    
    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Relaciones
    public function especialidades()
    {
        return $this->belongsToMany(
            Especialidad::class,
            'tecnico_especialidades',
            'id_tecnico',
            'id_especialidad'
        )->withPivot('nivel');
    }

    public function servicios()
    {
        return $this->belongsToMany(
            Servicio::class,
            'servicio_tecnicos',
            'id_tecnico',
            'id_servicio'
        )->withPivot('fecha_asignacion', 'rol')
          ->withTimestamps();
    }

    // Scope para técnicos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Obtener carga de trabajo actual
    public function getCargaTrabajoAttribute()
    {
        return $this->servicios()
            ->whereHas('estadoActual', function($q) {
                $q->whereIn('estado', ['Recibido', 'Diagnosticando', 'Reparando', 'Esperando repuestos']);
            })
            ->count();
    }
}