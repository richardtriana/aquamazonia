<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alimento extends Model
{
    protected $table = 'alimentos';

    protected $fillable = [
        'alimento',
        'costo_kg',
        'warehouse_id',
        'category_id',
        'brand_id',
        'provider_id',
        'cantidad',
        'cantidad_bulto', // <-- Nuevo campo agregado
        'cantidad_minima',
        'cantidad_maxima',
        'inventario',
        'activo' 
    ];

    protected $with = [
        'warehouse',
        'provider',
        'category',
        'brand'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function category()
    {
        return $this->belongsTo(FoodCategory::class, 'category_id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function siembras()
    {
        return $this->belongsToMany(Siembra::class, 'siembra_alimentos', 'alimento_id', 'siembra_id')
            ->withPivot('activo')
            ->withTimestamps();
    }
}