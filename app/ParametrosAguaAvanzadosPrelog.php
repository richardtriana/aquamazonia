<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParametrosAguaAvanzadosPrelog extends Model
{
    protected $table = 'parametros_agua_avanzados_prelogs';

    protected $fillable = [
        'registered_user_id',
        'id_siembra',
        'fecha_parametro',
        'ph',
        'amonio',
        'nitrito',
        'nitrato',
        'otros',
        'temperatura',
        '12_am',
        '4_am',      // NUEVO: agregado
        '7_am',      // Mantenido (puede eliminarse si no se usa)
        '4_pm',      // NUEVO: agregado
        '8_pm',
        'tipo',
        'descripcion',
        'fecha_registro',
        'hora_registro',
    ];

    protected $casts = [
        'fecha_parametro' => 'date',
        'fecha_registro' => 'date',
        'ph' => 'decimal:3',
        'amonio' => 'decimal:3',
        'nitrito' => 'decimal:3',
        'nitrato' => 'decimal:3',
        'temperatura' => 'decimal:2', // Cambiado a 2 decimales para coincidir con CalidadAgua
        '12_am' => 'decimal:2',      // Cambiado a 2 decimales
        '4_am' => 'decimal:2',       // NUEVO: 2 decimales
        '7_am' => 'decimal:2',       // 2 decimales
        '4_pm' => 'decimal:2',       // NUEVO: 2 decimales
        '8_pm' => 'decimal:2',       // 2 decimales
    ];

    // Accessors y mutators para campos con números
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

    public function registeredUser()
    {
        return $this->belongsTo(RegisteredUser::class, 'registered_user_id');
    }

    public function siembra()
    {
        return $this->belongsTo(Siembra::class, 'id_siembra');
    }

    // Método para obtener todos los campos de horas en un array
    public function getHorasArray()
    {
        return [
            '12_am' => $this->{'12_am'},
            '4_am' => $this->{'4_am'},
            '7_am' => $this->{'7_am'},
            '4_pm' => $this->{'4_pm'},
            '8_pm' => $this->{'8_pm'},
        ];
    }

    // Método para establecer múltiples horas
    public function setHorasArray(array $horas)
    {
        foreach ($horas as $hora => $valor) {
            if (property_exists($this, $hora) || in_array($hora, ['12_am', '4_am', '7_am', '4_pm', '8_pm'])) {
                $this->{$hora} = $valor;
            }
        }
    }

    // Scope para consultas por día
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('fecha_parametro', $date);
    }

    // Scope para consultas por siembra
    public function scopeForSiembra($query, $siembraId)
    {
        return $query->where('id_siembra', $siembraId);
    }

    // Scope para consultas por usuario
    public function scopeForUser($query, $userId)
    {
        return $query->where('registered_user_id', $userId);
    }

    // Método para formatear datos para respuesta API
    public function toApiArray()
    {
        return [
            'id' => $this->id,
            'fecha_parametro' => $this->fecha_parametro,
            'temperatura' => $this->temperatura,
            '12_am' => $this->{'12_am'},
            '4_am' => $this->{'4_am'},
            '7_am' => $this->{'7_am'},
            '4_pm' => $this->{'4_pm'},
            '8_pm' => $this->{'8_pm'},
            'ph' => $this->ph,
            'amonio' => $this->amonio,
            'nitrito' => $this->nitrito,
            'nitrato' => $this->nitrato,
            'otros' => $this->otros,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
            'fecha_registro' => $this->fecha_registro,
            'hora_registro' => $this->hora_registro,
            'siembra' => $this->siembra ? [
                'id' => $this->siembra->id,
                'nombre_siembra' => $this->siembra->nombre_siembra,
                'contenedor' => $this->siembra->contenedor,
            ] : null,
            'usuario' => $this->registeredUser ? [
                'id' => $this->registeredUser->id,
                'name' => $this->registeredUser->name,
                'email' => $this->registeredUser->email,
            ] : null,
        ];
    }
}