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
        Schema::table('aduans', function (Blueprint $table) {
            // Indeks komposit untuk filter status, urgensi, dan urutan waktu
            $table->index(['status', 'urgensi', 'created_at'], 'idx_aduans_status_urgensi_created');
            
            // Indeks komposit untuk filter antrean per instansi dinas OPD
            $table->index(['dinas_id', 'status', 'created_at'], 'idx_aduans_dinas_status_created');
            
            // Indeks spasial koordinat untuk kueri peta & heatmap
            $table->index(['latitude', 'longitude'], 'idx_aduans_lat_lng');
            
            // Indeks kategori & created_at
            $table->index('kategori', 'idx_aduans_kategori');
            $table->index('created_at', 'idx_aduans_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aduans', function (Blueprint $table) {
            $table->dropIndex('idx_aduans_status_urgensi_created');
            $table->dropIndex('idx_aduans_dinas_status_created');
            $table->dropIndex('idx_aduans_lat_lng');
            $table->dropIndex('idx_aduans_kategori');
            $table->dropIndex('idx_aduans_created_at');
        });
    }
};
