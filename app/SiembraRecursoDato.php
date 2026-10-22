<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiembraRecursoDato extends Model
{
    protected $table = 'siembra_recurso_datos';

    protected $fillable = [
        'siembra_id',
        'recurso_id',
        'tipo_actividad_id',
        'minutos',
        'minutos_hombre',
        'detalles',
    ];

    public function siembra()
    {
        return $this->belongsTo(Siembra::class, 'siembra_id');
    }

    public function recurso()
    {
        return $this->belongsTo(Recursos::class, 'recurso_id');
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'tipo_actividad_id');
    }
}
