<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{

    protected $with = [
        'provider',
        'detailPurchase'
    ];
    public function detailPurchase()
    {
        return $this->hasMany(DetailPurchase::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

}
