<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['nama', 'isi', 'rating', 'disetujui'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'disetujui' => 'boolean'];
    }
}
