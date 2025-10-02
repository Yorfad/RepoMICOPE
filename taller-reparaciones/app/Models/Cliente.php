<?php
// ================================================================
// app/Models/Cliente.php
// ================================================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    
    protected $fillable = [
        'nombre',
        'telefono',
        'email',
        'direccion',
        'id_municipio',
        'origen',
        'visitas_totales',
        'fecha_registro'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'visitas_totales' => 'integer'
    ];

    // Relaciones
    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'id_municipio', 'id_municipio');
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'id_cliente', 'id_cliente');
    }

    // Scope para filtrar por origen
    public function scopeOrigen($query, $origen)
    {
        return $query->where('origen', $origen);
    }

    // Accessor para obtener servicios del cliente
    public function servicios()
    {
        return Servicio::whereHas('equipo', function($q) {
            $q->where('id_cliente', $this->id_cliente);
        });
    }
}
