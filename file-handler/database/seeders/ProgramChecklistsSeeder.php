<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Checklists;
use App\Models\Programs;

class ProgramChecklistsSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Extra checklist names only for specific programs
        $extraNames = ["MCDC Endorsement", "MCDO", "PCC"];

        // ✅ Common checklist names for all programs, excluding the extra ones
        $commonNames = [
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
            "MDRRMO Certification"
        ];

        // ✅ Get IDs
        $common = Checklists::whereIn('name', $commonNames)->pluck('id')->toArray();
        $extra = Checklists::whereIn('name', $extraNames)->pluck('id')->toArray();

        // ✅ Attach to programs
        Programs::all()->each(function ($program) use ($common, $extra) {
            $program->checklists()->syncWithoutDetaching($common);

            if (in_array($program->name, ['LICAP', 'PCLRP'])) {
                $program->checklists()->syncWithoutDetaching($extra);
            }
        });
    }
}
