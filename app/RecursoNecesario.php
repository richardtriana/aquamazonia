<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecursoNecesario extends Model
{
    protected $table = 'recursos_necesarios';

    protected $fillable = [
        'registered_user_id',
        'id_recurso',
        'id_alimento',
        'tipo_actividad',
        'fecha_ra',
        'horas_hombre',
        'minutos_hombre',
        'cantidad_recurso',
        'cant_manana',
        'cant_tarde',
        'conv_alimenticia',
        'detalles',
        'costo_minutos_hombre',
        'costo_recurso',
        'costo_alimento',
        'siembra_id' // 👈🔥 ESTE FALTABA
    ];
}
