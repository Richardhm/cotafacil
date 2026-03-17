<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendVerificationEmail implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue,Dispatchable;

    protected $user;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handleold(): void
    {
        //$this->user->sendEmailVerificationNotification();
        //$this->user->notify(new CustomVerifyEmail());
    }

    public function handle()
    {
        try {
            // Tenta enviar o e-mail de forma assíncrona usando o Mail
            Mail::to($this->user->email)->send(new VerificationEmail($this->user));
            \Log::info("E-mail de verificação enviado para: {$this->user->email}");
        } catch (\Exception $e) {
            // Se der erro, log o problema
            \Log::error("Erro ao enviar e-mail para {$this->user->email}: {$e->getMessage()}");
        }
    }

}
