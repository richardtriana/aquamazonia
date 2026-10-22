<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiembraAlimento extends Model
{
    // Nombre de la tabla
    protected $table = 'siembra_alimentos';

    protected $fillable = [
        'siembra_id',
        'alimento_id',
    ];

    /**
     * Relación: una fila pertenece a una siembra.
     */
    public function siembra()
    {
        return $this->belongsTo(Siembra::class, 'siembra_id');
    }

    /**
     * Relación: una fila pertenece a un alimento.
     */
    public function alimento()
    {
        return $this->belongsTo(Alimento::class, 'alimento_id');
    }
}
