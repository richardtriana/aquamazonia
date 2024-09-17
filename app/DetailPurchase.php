<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPurchase extends Model
{
    use HasFactory;

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }
}
