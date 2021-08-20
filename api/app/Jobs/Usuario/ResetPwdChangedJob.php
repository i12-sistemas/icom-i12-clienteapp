<?php

namespace App\Jobs\Usuario;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Mail;
use App\Mail\Mail\Usuario\ResetPwdChanged;
use App\Models\UsuarioResetPwdTokens;

class ResetPwdChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(UsuarioResetPwdTokens $token)
    {
        $this->token = $token;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new ResetPwdChanged($this->token);
        Mail::send($email);
    }
}
