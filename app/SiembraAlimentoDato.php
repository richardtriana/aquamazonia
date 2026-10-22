<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiembraAlimentoDato extends Model
{
    protected $table = 'siembra_alimento_datos';

    protected $fillable = [
        'siembra_id',
        'alimento_id',
        'minutos_hombre',
        'conv_alimenticia',
        'detalles',
    ];

    public function siembra()
    {
        return $this->belongsTo(Siembra::class, 'siembra_id');
    }

    public function alimento()
    {
        return $this->belongsTo(Alimento::class, 'alimento_id');
    }
}
