<?php

namespace App\Mail;

use App\Models\Band;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewInvite extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Band $band;

    public function __construct(Band $band)
    {
        $this->band = $band;
    }

    public function build(): self
    {
        return $this->view('mails.new-invite')->with(['band' => $this->band]);
    }
}
