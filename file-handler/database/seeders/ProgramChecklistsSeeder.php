<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Checklists;
use App\Models\Programs;
class ProgramChecklistsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $common = Checklists::whereIn('name',[
            "Letter",
            "Project proposal",
            "Financial Plan",
            "GA Resolution_ Avail",
            "GA Resolution 25percent",
            "Board Resolution Signatories",
            "BOD Resolution ExOfficio",
            "Certified Members List",
            "Secretary Certificate",
            "Disclosure_Statement",
            "Sworn Affidavit",
            "Past Projects",
            "Surety Bond",
            "CDA Reregistration Certificate",
            "Certificate of Compliance",
            "Bio Data",
            "Photocopy of 2 Valid Id",
            "Photocopy of BIR official receipt",
            "Audited F or S for last 3 years and latest CAPR",
            "Authenticated copy of Articles and ByLaws of Cooperative",
            "LGU or SP Accreditation",
            "MAO Certificate",
            "MDRRMO Certification",
        ])->pluck('id')->toArray();

        $extra = Checklists::whereIn('name',[
            "MCDC Endorsement",
            "MCDO",
            "PCC"
        ])->pluck('id')->toArray();
        
        $programs = Programs::all();
        foreach ($programs as $program) {
            $program->checklists()->syncWithoutDetaching($common);
            if ($program->name === 'LICAP') {
                $program->checklists()->syncWithoutDetaching($extra);
            }
            if ($program->name === 'PCLRP') {
                $program->checklists()->syncWithoutDetaching($extra);
            }
        }
    }
}
