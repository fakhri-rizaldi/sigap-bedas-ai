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
        Schema::create('koreksi_kategoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aduan_id')->constrained('aduans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kategori_lama');
            $table->string('kategori_baru');
            $table->foreignId('dinas_lama_id')->nullable()->constrained('dinas')->nullOnDelete();
            $table->foreignId('dinas_baru_id')->nullable()->constrained('dinas')->nullOnDelete();
            $table->text('alasan_koreksi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('koreksi_kategoris');
    }
};
