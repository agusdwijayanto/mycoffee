<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = ['kode', 'tipe', 'nilai', 'aktif', 'berlaku_sampai'];

    protected function casts(): array
    {
        return ['nilai' => 'integer', 'aktif' => 'boolean', 'berlaku_sampai' => 'date'];
    }

    public function masihBerlaku(): bool
    {
        return $this->aktif && ($this->berlaku_sampai === null || ! $this->berlaku_sampai->isPast());
    }
}
