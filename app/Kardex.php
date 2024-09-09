<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kardex extends Model
{
    use HasFactory;

    protected $fillable = [
        'alimento_id',
        'recurso_id',
        'tipo',
        'descripcion',
        'fecha',
        'cantidad'
    ];


    public function alimento()
    {
        return $this->belongsTo(Alimento::class, 'alimento_id');
    }

    public function recurso()
    {
        return $this->belongsTo(Recursos::class, 'recurso_id');
    }
}
