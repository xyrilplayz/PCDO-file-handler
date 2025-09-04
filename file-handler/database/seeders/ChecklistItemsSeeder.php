<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistItemsSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
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

        foreach ($items as $item) {
            DB::table('checklist_items')->insert([
                'name' => $item,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }// add another checklist for livelihood program
}
