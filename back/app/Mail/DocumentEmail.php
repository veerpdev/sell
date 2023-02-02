<?php

namespace App\Mail;

use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DocumentEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $organizationName;
    public $documentPath;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($organizationName, $documentPath)
    {
        $this->organizationName = $organizationName;
        $this->documentPath = $documentPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.document')
            ->subject("Attached Document")
            ->attach($this->documentPath, [
                'as' => 'document.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
