<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DinasSeeder::class,
            SampleAduanSeeder::class,
        ]);

        $dputr = \App\Models\Dinas::where('kode_dinas', 'DPUTR')->first();
        $dlh = \App\Models\Dinas::where('kode_dinas', 'DLH')->first();
        $dinsos = \App\Models\Dinas::where('kode_dinas', 'DINSOS')->first();
        $satpolpp = \App\Models\Dinas::where('kode_dinas', 'SATPOLPP')->first();

        // 1. Super Admin SIGAP
        User::updateOrCreate(
            ['email' => 'admin@bandungkab.go.id'],
            [
                'name' => 'Administrator SIGAP',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Petugas DPUTR
        if ($dputr) {
            User::updateOrCreate(
                ['email' => 'petugas.dputr@bandungkab.go.id'],
                [
                    'name' => 'Staf Dinas PUTR',
                    'dinas_id' => $dputr->id,
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3. Petugas DLH
        if ($dlh) {
            User::updateOrCreate(
                ['email' => 'petugas.dlh@bandungkab.go.id'],
                [
                    'name' => 'Staf Dinas Lingkungan Hidup',
                    'dinas_id' => $dlh->id,
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
