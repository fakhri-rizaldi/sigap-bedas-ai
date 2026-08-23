<?php

namespace Tests\Feature;

use App\Models\Aduan;
use App\Models\Dinas;
use App\Models\KategoriDinasMapping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseAndModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_dinas_and_kategori_mappings_can_be_seeded_and_queried(): void
    {
        $this->seed(\Database\Seeders\DinasSeeder::class);

        $this->assertDatabaseCount('dinas', 4);
        $this->assertDatabaseCount('kategori_dinas_mappings', 4);

        $dputr = Dinas::where('kode_dinas', 'DPUTR')->first();
        $this->assertNotNull($dputr);
        $this->assertCount(1, $dputr->kategoriMappings);
        $this->assertEquals('Jalan Rusak', $dputr->kategoriMappings->first()->kategori);
    }

    public function test_aduan_generates_unique_ticket_code_automatically(): void
    {
        $this->seed(\Database\Seeders\DinasSeeder::class);

        $aduan1 = Aduan::factory()->create();
        $aduan2 = Aduan::factory()->create();

        $this->assertNotNull($aduan1->kode_tiket);
        $this->assertNotNull($aduan2->kode_tiket);
        $this->assertNotEquals($aduan1->kode_tiket, $aduan2->kode_tiket);
        $this->assertStringStartsWith('BDS-', $aduan1->kode_tiket);
    }

    public function test_aduan_relationship_with_dinas(): void
    {
        $this->seed(\Database\Seeders\DinasSeeder::class);

        $aduan = Aduan::factory()->create();
        $this->assertInstanceOf(Dinas::class, $aduan->dinas);
    }
}
