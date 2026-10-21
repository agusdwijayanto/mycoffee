<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = ['judul', 'foto'];

    public function fotoUrl(): string
    {
        return asset('images/gallery/'.$this->foto);
    }
}
