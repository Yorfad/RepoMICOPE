<?php

// ================================================================
// app/Models/Repuesto.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repuesto extends Model
{
    use HasFactory;

    protected $table = 'repuestos';
    protected $primaryKey = 'id_repuesto';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'cantidad',
        'costo_unitario',
        'precio_unitario',
        'activo'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'costo_unitario' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'activo' => 'boolean'
    ];

    public function servicios()
    {
        return $this->belongsToMany(
            Servicio::class,
            'servicio_repuestos',
            'id_repuesto',
            'id_servicio'
        )->withPivot('cantidad', 'costo_total', 'precio_total');
    }

    // Scope para repuestos activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para repuestos con bajo stock
    public function scopeBajoStock($query, $minimo = 5)
    {
        return $query->where('cantidad', '<=', $minimo)->where('activo', true);
    }

    // Calcular margen de ganancia
    public function getMargenAttribute()
    {
        if ($this->costo_unitario == 0) return 0;
        return (($this->precio_unitario - $this->costo_unitario) / $this->costo_unitario) * 100;
    }
}
