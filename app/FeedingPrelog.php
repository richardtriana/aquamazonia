<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FeedingPrelog extends Model
{
    protected $table = 'feeding_prelogs';

    protected $fillable = [
        'registered_user_id',
        'id_siembra',
        'id_recurso',      
        'id_alimento',
        'fecha_ra',
        'cant_manana',
        'cant_tarde',
        'minutos_hombre',
        'conv_alimenticia',
        'detalles',
        'tipo_actividad',
        'tipo',
        'descripcion',
    ];

    protected $casts = [
        'fecha_ra'         => 'date',
        'cant_manana'      => 'decimal:2',
        'cant_tarde'       => 'decimal:2',
        'minutos_hombre'   => 'decimal:2',
        'conv_alimenticia' => 'decimal:3',
    ];

    public function registeredUser()
    {
        return $this->belongsTo(RegisteredUser::class, 'registered_user_id');
    }

    // Relación con el recurso - CORREGIDO: usar Recursos (modelo existente)
    public function recurso()
    {
        return $this->belongsTo(Recursos::class, 'id_recurso');
    }

    // Relación con el alimento (si existe el modelo Alimento)
    public function alimento()
    {
        return $this->belongsTo(Alimento::class, 'id_alimento');
    }

    // Relación con la siembra (si existe el modelo Siembra)
    public function siembra()
    {
        return $this->belongsTo(Siembra::class, 'id_siembra');
    }

    // Scope para consultas por día
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('fecha_ra', $date);
    }

    // Scope para consultas por siembra
    public function scopeForSiembra($query, $siembraId)
    {
        return $query->where('id_siembra', $siembraId);
    }

    // Scope para consultas por recurso
    public function scopeForRecurso($query, $recursoId)
    {
        return $query->where('id_recurso', $recursoId);
    }

    // Scope para consultas por alimento
    public function scopeForAlimento($query, $alimentoId)
    {
        return $query->where('id_alimento', $alimentoId);
    }

    // Método para calcular el total de la cantidad (mañana + tarde)
    public function getCantidadTotalAttribute()
    {
        return (float) $this->cant_manana + (float) $this->cant_tarde;
    }

    // Método para convertir minutos a horas (opcional)
    public function getHorasHombreAttribute()
    {
        return $this->minutos_hombre / 60;
    }

    // Método para verificar si tiene recurso asociado
    public function hasRecurso()
    {
        return !is_null($this->id_recurso);
    }

    // Método para verificar si tiene alimento asociado
    public function hasAlimento()
    {
        return !is_null($this->id_alimento);
    }

    // Método para verificar si es de tipo AIREACION
    public function isAireacion()
    {
        return $this->tipo === 'AIREACION';
    }

    // Método para verificar si es de tipo ALIMENTACION
    public function isAlimentacion()
    {
        return is_null($this->tipo) || $this->tipo === 'ALIMENTACION' || $this->tipo !== 'AIREACION';
    }

    // Método para obtener el nombre del tipo de actividad
    public function getTipoActividadNombreAttribute()
    {
        if ($this->tipo_actividad == 12) {
            return 'Aireación Mecánica';
        }
        
        if ($this->tipo_actividad == 1) {
            return 'Alimentación';
        }
        
        return 'Tipo ' . $this->tipo_actividad;
    }

    // Método para obtener el nombre del recurso (si existe)
    public function getRecursoNombreAttribute()
    {
        if ($this->recurso && isset($this->recurso->recurso)) {
            return $this->recurso->recurso;
        }
        
        return $this->id_recurso ? 'Recurso #' . $this->id_recurso : null;
    }

    // Método para obtener el nombre del alimento (si existe)
    public function getAlimentoNombreAttribute()
    {
        if ($this->alimento && isset($this->alimento->alimento)) {
            return $this->alimento->alimento;
        }
        
        return $this->id_alimento ? 'Alimento #' . $this->id_alimento : null;
    }

    // Método para obtener el nombre de la siembra (si existe)
    public function getSiembraNombreAttribute()
    {
        if ($this->siembra && isset($this->siembra->nombre_siembra)) {
            return $this->siembra->nombre_siembra;
        }
        
        return $this->id_siembra ? 'Siembra #' . $this->id_siembra : null;
    }

    // Método para obtener el nombre del registrador
    public function getRegistradorNombreAttribute()
    {
        if ($this->registeredUser) {
            return $this->registeredUser->name ?? $this->registeredUser->email ?? 'Registrador #' . $this->registered_user_id;
        }
        
        return 'Registrador #' . $this->registered_user_id;
    }

    // Método para calcular el costo de minutos hombre (si se implementa)
    public function getCostoMinutosHombreAttribute()
    {
        // Implementar lógica de costo por minuto aquí
        return 0;
    }

    // Método para calcular el costo total del recurso (si se implementa)
    public function getCostoRecursoAttribute()
    {
        if ($this->recurso && isset($this->recurso->costo)) {
            // Convertir minutos a horas si el costo es por hora
            $horas = $this->minutos_hombre / 60;
            return $horas * $this->recurso->costo;
        }
        
        return 0;
    }

    // Método para calcular el costo total del alimento (si se implementa)
    public function getCostoAlimentoAttribute()
    {
        if ($this->alimento && isset($this->alimento->costo_kg)) {
            $cantidadTotal = $this->cantidadTotal;
            return $cantidadTotal * $this->alimento->costo_kg;
        }
        
        return 0;
    }

    // Método para calcular el costo total (suma de todos los costos)
    public function getCostoTotalAttribute()
    {
        return $this->costo_minutos_hombre + $this->costo_recurso + $this->costo_alimento;
    }

    // Método para sincronizar con RecursoNecesario (opcional)
    public function syncToRecursoNecesario()
    {
        // Verificar si el modelo RecursoNecesario existe
        if (!class_exists('App\RecursoNecesario')) {
            return null;
        }
        
        return \App\RecursoNecesario::create([
            'registered_user_id' => $this->registered_user_id,
            'id_recurso' => $this->id_recurso,
            'id_alimento' => $this->id_alimento,
            'tipo_actividad' => $this->tipo_actividad,
            'fecha_ra' => $this->fecha_ra,
            'horas_hombre' => $this->horas_hombre,
            'minutos_hombre' => $this->minutos_hombre,
            'cantidad_recurso' => $this->cantidadTotal,
            'cant_manana' => $this->cant_manana,
            'cant_tarde' => $this->cant_tarde,
            'conv_alimenticia' => $this->conv_alimenticia,
            'detalles' => $this->detalles,
            'siembra_id' => $this->id_siembra,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
            // Los campos de costo pueden calcularse después
            'costo_minutos_hombre' => $this->costo_minutos_hombre,
            'costo_recurso' => $this->costo_recurso,
            'costo_alimento' => $this->costo_alimento,
        ]);
    }

    // Método para formatear la fecha para mostrar
    public function getFechaFormateadaAttribute()
    {
        return $this->fecha_ra->format('d/m/Y');
    }

    // Método para obtener el día de la semana
    public function getDiaSemanaAttribute()
    {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        return $dias[$this->fecha_ra->dayOfWeek];
    }

    // Método para determinar si es registro de la mañana
    public function isManana()
    {
        return !empty($this->cant_manana) && $this->cant_manana > 0;
    }

    // Método para determinar si es registro de la tarde
    public function isTarde()
    {
        return !empty($this->cant_tarde) && $this->cant_tarde > 0;
    }

    // Método para determinar si es registro completo (mañana y tarde)
    public function isCompleto()
    {
        return $this->isManana() && $this->isTarde();
    }

    // Scope para filtrar por tipo (AIREACION o ALIMENTACION)
    public function scopePorTipo($query, $tipo)
    {
        if ($tipo === 'AIREACION') {
            return $query->where('tipo', 'AIREACION');
        } elseif ($tipo === 'ALIMENTACION') {
            return $query->where(function($q) {
                $q->whereNull('tipo')
                  ->orWhere('tipo', '!=', 'AIREACION');
            });
        }
        
        return $query;
    }

    // Scope para filtrar registros de aireación
    public function scopeAireacion($query)
    {
        return $query->where('tipo', 'AIREACION');
    }

    // Scope para filtrar registros de alimentación
    public function scopeAlimentacion($query)
    {
        return $query->where(function($q) {
            $q->whereNull('tipo')
              ->orWhere('tipo', '!=', 'AIREACION');
        });
    }

    // Scope para filtrar registros con recurso
    public function scopeConRecurso($query)
    {
        return $query->whereNotNull('id_recurso');
    }

    // Scope para filtrar registros con alimento
    public function scopeConAlimento($query)
    {
        return $query->whereNotNull('id_alimento');
    }

    // Scope para filtrar por rango de fechas
    public function scopeRangoFechas($query, $fechaInicio, $fechaFin = null)
    {
        if (!$fechaFin) {
            $fechaFin = $fechaInicio;
        }
        
        return $query->whereBetween('fecha_ra', [$fechaInicio, $fechaFin]);
    }
}