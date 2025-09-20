<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Checklists;

class ChecklistsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterChecklists = [
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
            "MCDC Endorsement",
            "MCDO",
            "PCC"
        ];

        foreach ($masterChecklists as $name) {
            Checklists::create(['name' => $name]);
        }
    }
}
