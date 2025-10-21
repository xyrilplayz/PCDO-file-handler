<?php

namespace App\Mail;

use App\Models\Cooperative;
use App\Models\Programs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoopProgramEnrolled extends Mailable
{
    use Queueable, SerializesModels;

    public $cooperative;
    public $program;

    public function __construct(Cooperative $cooperative, Programs $program)
    {
        $this->cooperative = $cooperative;
        $this->program = $program;
    }

    public function build()
    {
        return $this->subject('Program Enrollment Confirmation')
                    ->view('coop_program_enrolled');
    }
}
