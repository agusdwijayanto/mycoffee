<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id', 'menu_id', 'nama_snapshot', 'harga_snapshot', 'harga_modal_snapshot',
        'qty', 'size', 'sugar_level', 'ice_level', 'note', 'addons_snapshot', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'harga_snapshot' => 'integer',
            'harga_modal_snapshot' => 'integer',
            'qty' => 'integer',
            'subtotal' => 'integer',
            'addons_snapshot' => 'array',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /** Ringkasan catatan item untuk struk/detail, contoh: "Large, Manis: Low, Es: Half, Tanpa krim". */
    public function ringkasanCatatan(): string
    {
        $bagian = [];

        if ($this->size && $this->size !== 'Regular') {
            $bagian[] = $this->size;
        }
        if ($this->sugar_level) {
            $bagian[] = 'Manis: '.$this->sugar_level;
        }
        if ($this->ice_level) {
            $bagian[] = 'Es: '.$this->ice_level;
        }
        foreach ($this->addons_snapshot ?? [] as $addon) {
            $bagian[] = '+ '.$addon['nama'];
        }
        if ($this->note) {
            $bagian[] = $this->note;
        }

        return implode(', ', $bagian);
    }
}
