<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['judul', 'kategori', 'nominal', 'tanggal', 'catatan'];

    protected function casts(): array
    {
        return ['nominal' => 'integer', 'tanggal' => 'date'];
    }
}
