<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dinas extends Model
{
    use HasFactory;

    protected $table = 'dinas';

    protected $fillable = [
        'nama_dinas',
        'kode_dinas',
        'kontak_email',
        'kontak_telepon',
        'alamat_kantor',
    ];

    /**
     * Relasi ke laporan aduan warga.
     */
    public function aduans(): HasMany
    {
        return $this->hasMany(Aduan::class, 'dinas_id');
    }

    /**
     * Relasi ke mapping kategori dinas.
     */
    public function kategoriMappings(): HasMany
    {
        return $this->hasMany(KategoriDinasMapping::class, 'dinas_id');
    }
}
