<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'name' => 'USAD',
                'term_months' => 24,
                'grace_period' => 4,
                'min_amount' => 201000,
                'max_amount' => 300000,
            ],
            [
                'name' => 'SULONG',
                'term_months' => 24,
                'grace_period' => 4,
                'min_amount' => 301000,
                'max_amount' => 500000,
            ],
            [
                'name' => 'LICAP',
                'term_months' => 16,
                'grace_period' => 4,
                'min_amount' => 100000,
                'max_amount' => 200000,
            ],
            [
                'name' => 'COPSE',
                'term_months' => 24,
                'grace_period' => 4,
                'min_amount' => 501000,
                'max_amount' => 750000,
            ],
            [
                'name' => 'PCLRP',
                'term_months' => 16,
                'grace_period' => 4,
                'min_amount' => 100000,
                'max_amount' => 200000,
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(['name' => $program['name']], $program);
        }
    }
}
