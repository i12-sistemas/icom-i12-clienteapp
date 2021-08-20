<?php

namespace App\Jobs\Usuario;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Mail;
use App\Mail\Mail\Usuario\ResetPwdRequest;
use App\Models\UsuarioResetPwdTokens;

class ResetPwdRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;

    public function __construct(UsuarioResetPwdTokens $token)
    {
        $this->token = $token;
    }

    public function handle()
    {
        $email = new ResetPwdRequest($this->token);
        Mail::send($email);
    }
}
