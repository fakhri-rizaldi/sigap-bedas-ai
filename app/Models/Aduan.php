<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aduan extends Model
{
    use HasFactory;

    protected $table = 'aduans';

    protected $fillable = [
        'kode_tiket',
        'teks_aduan',
        'kategori',
        'confidence_kategori',
        'urgensi',
        'alasan_urgensi',
        'dinas_id',
        'status',
        'latitude',
        'longitude',
        'alamat',
        'foto_path',
        'nama_pelapor',
        'kontak_pelapor',
        'email_pelapor',
        'sumber_klasifikasi',
        'perlu_review',
        'kategori_model_lokal',
        'confidence_model_lokal',
        'catatan_petugas',
    ];

    protected $casts = [
        'confidence_kategori' => 'float',
        'confidence_model_lokal' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'perlu_review' => 'boolean',
    ];

    /**
     * Boot model events: generate kode tiket unik otomatis.
     */
    protected static function booted(): void
    {
        static::creating(function (Aduan $aduan) {
            if (empty($aduan->kode_tiket)) {
                $aduan->kode_tiket = static::generateKodeTiket();
            }
        });
    }

    /**
     * Format generator kode tiket: BDS-YYYYMMDD-XXXX
     */
    public static function generateKodeTiket(): string
    {
        $today = Carbon::now()->format('Ymd');
        $prefix = "BDS-{$today}-";

        $lastRecord = static::where('kode_tiket', 'LIKE', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRecord && preg_match('/-(\d{4})$/', $lastRecord->kode_tiket, $matches)) {
            $nextSequence = intval($matches[1]) + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke dinas tujuan.
     */
    public function dinas(): BelongsTo
    {
        return $this->belongsTo(Dinas::class, 'dinas_id');
    }

    /**
     * Scope filter untuk tiket yang butuh review staf.
     */
    public function scopePerluReview(Builder $query): Builder
    {
        return $query->where('perlu_review', true);
    }

    /**
     * Scope filter status aduan.
     */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope filter kategori aduan.
     */
    public function scopeKategori(Builder $query, string $kategori): Builder
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Scope filter tingkat urgensi.
     */
    public function scopeUrgensi(Builder $query, string $urgensi): Builder
    {
        return $query->where('urgensi', $urgensi);
    }
}
