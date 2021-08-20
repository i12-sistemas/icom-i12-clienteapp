<?php

namespace App\Mail\Mail\Usuario;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Exception;

use App\Models\UsuarioResetPwdTokens;

class ResetPwdChanged extends Mailable
{
    use Queueable, SerializesModels;

    protected $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(UsuarioResetPwdTokens $token)
    {
        $this->token = $token;

        if(!$this->token)
          throw new Exception('Token não informado');

        if($this->token->processado !== 1)
          throw new Exception('Senha não foi alterada');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->token->email)
        ->subject('Aviso de alteração de senha - Sistema ' . env('APP_NAME') )
        ->markdown('emails.usuario.resetpwdchanged')
        ->with([
          'token' =>  $this->token
        ]);
    }
}
