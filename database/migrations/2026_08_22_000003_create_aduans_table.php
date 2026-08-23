<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aduans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_tiket', 30)->unique()->index();
            $table->text('teks_aduan');
            $table->string('kategori');
            $table->decimal('confidence_kategori', 5, 4)->nullable();
            $table->enum('urgensi', ['Rendah', 'Sedang', 'Tinggi', 'Darurat'])->default('Sedang');
            $table->text('alasan_urgensi')->nullable();
            $table->foreignId('dinas_id')->nullable()->constrained('dinas')->nullOnDelete();
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru')->index();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('nama_pelapor')->nullable();
            $table->string('kontak_pelapor')->nullable();
            $table->string('email_pelapor')->nullable();
            $table->string('sumber_klasifikasi')->default('gemini_api');
            $table->boolean('perlu_review')->default(false)->index();
            $table->string('kategori_model_lokal')->nullable();
            $table->decimal('confidence_model_lokal', 5, 4)->nullable();
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aduans');
    }
};
