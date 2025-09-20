<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Municipality;
use App\Models\Province;
use Illuminate\Database\Seeder;

class MunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            'Palawan' => [
                'Aborlan',
                'Agutaya',
                'Araceli',
                'Balabac',
                'Bataraza',
                'Brooke\'s Point',
                'Busuanga',
                'Cagayancillo',
                'Coron',
                'Culion',
                'Cuyo',
                'Dumaran',
                'El Nido',
                'Kalayaan',
                'Linapacan',
                'Magsaysay',
                'Narra',
                'Quezon',
                'Roxas',
                'San Vicente',
                'Sofronio Española',
                'Taytay',
            ],
            'AnotherProvince' => [
                'Municipality1',
                'Municipality2',
                'Municipality3',
            ],
        ];
        foreach ($provinces as $provinceName => $municipalities) {
            $province = Province::firstOrCreate(['name' => $provinceName]);
            foreach ($municipalities as $municipalityName) {
                Municipality::firstOrCreate([
                    'name' => $municipalityName,
                    'province_id' => $province->id,
                ]);
            }
        }
    }
}
