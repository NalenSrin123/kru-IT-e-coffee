<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModifierGroup extends Model
{
    use HasFactory;

    protected $table = 'product_modifier_groups';

    protected $guarded = [];
}
