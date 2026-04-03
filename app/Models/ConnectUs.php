<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectUs extends Model
{
    protected $table = 'connect_us';

    protected $fillable = [
        'address',
        'phone',
        'email',
    ];
}
