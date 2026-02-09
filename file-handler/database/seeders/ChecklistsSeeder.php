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
            "Project Proposal Approved/signed by it officer",
            "Working Financial plan & sources and details of proponents equity participation in the project",
            "General Assembly Resolution to avail of Programs",
            "General Assembly Resolution authorizing the BODs",
            "Board Resolution of authorized Signatories to sign documents",
            "BOD Resolution Allowing the PCDOs designated representative/s to sit with the BODs Ex-Officio member",
            "Certified List of Members with share capital and loans",
            "Secretary Certificate to incumbent officers",
            "Disclosure Statement of other related business and extent of ownership",
            "Sworn Affidavit of the secretary",
            "List and photographs of similar projects previously completed by the cooperative",
            "Surety Bond of accountable officers",
            "CDA Reregistration Certificate",
            "Certificate of Compliance",
            "Bio Data of coop officers and management staffs",
            "Photocopy of 2 Valid IDs",
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
