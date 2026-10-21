<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    protected $fillable = ['nama', 'satuan', 'stok'];

    protected function casts(): array
    {
        return ['stok' => 'float'];
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_ingredients')
            ->withPivot('qty_per_porsi')->withTimestamps();
    }
}
