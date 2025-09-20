<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cooperative;
use App\Models\CoopDetail;
use App\Models\Municipality;

class CoopDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipalityIds = Municipality::pluck('id')->toArray();
        foreach (Cooperative::all() as $coop) {
            CoopDetail::create([
                'coop_id' => $coop->id,
                'municipality_id' => $municipalityIds[array_rand($municipalityIds)],
                'asset_size' => ['Micro', 'Small', 'Medium', 'Large', 'Unclassified'][rand(0, 4)],
                'coop_type' => ['Credit', 'Consumers', 'Producers', 'Marketing', 'Service', 'Multipurpose', 'Advocacy', 'Agrarian Reform', 'Bank', 'Diary', 'Education', 'Electric', 'Financial', 'Fishermen', 'Health Services', 'Housing', 'Insurance', 'Water Service', 'Workers', 'Others'][rand(0, 19)],
                'status_or_category' => ['Reporting', 'Non-Reporting', 'New'][rand(0, 2)],
                'bond_of_membership' => ['Residential', 'Insitutional', 'Associational', 'Occupational', 'Unspecified'][rand(0, 4)],
                'area_of_operation' => ['Municipal', 'Provincial'][rand(0, 1)],
                'citizenship' => ['Filipino', 'Others'][rand(0, 1)],
                'members_count' => rand(10, 500),
                'total_asset' => rand(100000, 5000000),
                'net_surplus' => rand(5000, 200000),
            ]);
        }
    }
}
