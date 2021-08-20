<?php

namespace App\Mail\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Exception;
use App\Models\ClienteUsuarioResetPwdTokens;

class UsuarioClienteResetPwdRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $token;

    public function __construct(ClienteUsuarioResetPwdTokens $token)
    {
        $this->token = $token;
        if(!$this->token) throw new Exception('Nenhum token não informado');
    }

    public function build()
    {
        return $this->to($this->token->clienteusuario->email, $this->token->clienteusuario->nome)
                    ->subject('Redefinição de senha do painel do cliente :: ' . env('APP_NAME'))
                    ->markdown('emails.cliente.resetpwdrequest')
                    ->with([
                        'token' =>  $this->token
                    ]);
    }
}
