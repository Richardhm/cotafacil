<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\User;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;

    /**
     * Cria uma nova mensagem
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Conteúdo de envio do e-mail.
     */
    public function build()
    {
        $url = route('verification.verify', [
            'id' => $this->user->id,
            'hash' => sha1($this->user->email),
        ]);

        return $this->subject('Verifique seu e-mail')
            ->view('emails.verification')
            ->with([
                'user' => $this->user,
                'verificationUrl' => $url,
            ]);
    }
}
