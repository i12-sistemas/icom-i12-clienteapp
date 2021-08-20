<?php

namespace App\Mail\Mail\Usuario;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Exception;

use App\Models\UsuarioResetPwdTokens;

class ResetPwdRequest extends Mailable
{
    use Queueable, SerializesModels;

    protected $token;

    public function __construct(UsuarioResetPwdTokens $token)
    {
        $this->token = $token;

        if(!$this->token)
          throw new Exception('Token não informado');

        if($this->token->expirado)
          throw new Exception('Token já processado ou expirado');
    }

    public function build()
    {

        return $this->to($this->token->email)
        ->subject('Redefinição de senha - Sistema ' . env('APP_NAME') )
        ->markdown('emails.usuario.resetpwdrequest')
        ->with([
          'token' =>  $this->token
        ]);

    }
}
