<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Social extends Model
{
    use HasFactory;

    protected $table = 'social_links';

    protected $fillable = [
        'name', 'url', 'img', 'is_active', 'created_by', 'updated_by'
    ];

    // Automatically track the user who creates or updates
    protected static function booted()
    {
        static::creating(function ($social) {
            $social->created_by = Auth::id();
        });

        static::updating(function ($social) {
            $social->updated_by = Auth::id();
        });
    }
}