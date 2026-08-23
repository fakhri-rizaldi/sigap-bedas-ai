<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KategoriDinasMapping extends Model
{
    use HasFactory;

    protected $table = 'kategori_dinas_mappings';

    protected $fillable = [
        'kategori',
        'dinas_id',
        'deskripsi',
    ];

    /**
     * Relasi ke dinas tujuan.
     */
    public function dinas(): BelongsTo
    {
        return $this->belongsTo(Dinas::class, 'dinas_id');
    }
}
