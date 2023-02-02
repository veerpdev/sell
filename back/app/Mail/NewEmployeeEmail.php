<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewEmployeeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $rawPassword;
    public $organizationName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, String $rawPassword)
    {
        $this->user = $user;
        $this->rawPassword = $rawPassword;
        $this->organizationName = $user->organization->name;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.newEmployee')
                    ->subject('Welcome to Aurora '. $this->user->first_name);
    }
}
