<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KoreksiKategori extends Model
{
    use HasFactory;

    protected $table = 'koreksi_kategoris';

    protected $fillable = [
        'aduan_id',
        'user_id',
        'kategori_lama',
        'kategori_baru',
        'dinas_lama_id',
        'dinas_baru_id',
        'alasan_koreksi',
    ];

    /**
     * Relasi ke Aduan terkait.
     */
    public function aduan(): BelongsTo
    {
        return $this->belongsTo(Aduan::class, 'aduan_id');
    }

    /**
     * Relasi ke User staf / admin yang melakukan koreksi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Dinas asal sebelum koreksi.
     */
    public function dinasLama(): BelongsTo
    {
        return $this->belongsTo(Dinas::class, 'dinas_lama_id');
    }

    /**
     * Relasi ke Dinas baru hasil rerouting koreksi.
     */
    public function dinasBaru(): BelongsTo
    {
        return $this->belongsTo(Dinas::class, 'dinas_baru_id');
    }
}
