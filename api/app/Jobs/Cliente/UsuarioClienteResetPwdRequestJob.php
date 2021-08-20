<?php

namespace App\Jobs\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Mail;
use App\Mail\Cliente\UsuarioClienteResetPwdRequestMail;
use App\Models\ClienteUsuarioResetPwdTokens;

class UsuarioClienteResetPwdRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token;

    public function __construct(ClienteUsuarioResetPwdTokens $token)
    {
        $this->token = $token;
    }

    public function handle()
    {
        $email = new UsuarioClienteResetPwdRequestMail($this->token);
        Mail::send($email);
    }
}
