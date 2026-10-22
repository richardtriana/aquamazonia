<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegisteredUser extends Model
{
    protected $table = 'registered_users';

    protected $fillable = [
        'name',
        'phone',
        'pin',
        'estado',
        'rol'
    ];
}
