<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'uom',
        'current_stock',
        'min_stock_level',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'min_stock_level' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function modifierRecipes(): HasMany
    {
        return $this->hasMany(ModifierRecipe::class);
    }

    public function productRecipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
