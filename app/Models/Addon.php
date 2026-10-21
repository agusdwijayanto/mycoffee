<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = ['nama', 'harga', 'aktif'];

    protected function casts(): array
    {
        return ['harga' => 'integer', 'aktif' => 'boolean'];
    }
}
