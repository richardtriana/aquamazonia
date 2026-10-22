<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalidadAgua extends Model
{
    protected $table = 'calidad_agua';

    protected $fillable = [
        'fecha_parametro',
        'id_contenedor',
        'id_siembra',
        'registered_user_id',
        '12_am',
        '4_am',
        '7_am',
        '4_pm',
        '8_pm', 
        'temperatura',
        'ph',
        'amonio',
        'nitrito',
        'nitrato',
        'otros',
        'tipo',
        'descripcion',
        'fecha_registro',
        'hora_registro'
    ];

    /**
     * Cast de atributos
     */
    protected $casts = [
        'fecha_parametro' => 'date',
        'fecha_registro' => 'date',
        '12_am' => 'decimal:2',
        '4_am' => 'decimal:2',
        '7_am' => 'decimal:2',
        '4_pm' => 'decimal:2',
        '8_pm' => 'decimal:2',
        'temperatura' => 'decimal:2',
        'ph' => 'decimal:3',
        'amonio' => 'decimal:3',
        'nitrito' => 'decimal:3',
        'nitrato' => 'decimal:3',
    ];

    /**
     * Atributos renombrados para columnas con números
     */
    public function get12AmAttribute($value)
    {
        return $value;
    }

    public function set12AmAttribute($value)
    {
        $this->attributes['12_am'] = $value;
    }

    public function get4AmAttribute($value)
    {
        return $value;
    }

    public function set4AmAttribute($value)
    {
        $this->attributes['4_am'] = $value;
    }

    public function get7AmAttribute($value)
    {
        return $value;
    }

    public function set7AmAttribute($value)
    {
        $this->attributes['7_am'] = $value;
    }

    public function get4PmAttribute($value)
    {
        return $value;
    }

    public function set4PmAttribute($value)
    {
        $this->attributes['4_pm'] = $value;
    }

    public function get8PmAttribute($value)
    {
        return $value;
    }

    public function set8PmAttribute($value)
    {
        $this->attributes['8_pm'] = $value;
    }

    /**
     * Relación con el contenedor
     */
    public function contenedor()
    {
        return $this->belongsTo('App\Contenedor', 'id_contenedor');
    }

    /**
     * Relación con la siembra
     */
    public function siembra()
    {
        return $this->belongsTo('App\Siembra', 'id_siembra');
    }

    /**
     * Relación con el usuario registrado
     */
    public function usuario()
    {
        return $this->belongsTo('App\RegisteredUser', 'registered_user_id');
    }
}