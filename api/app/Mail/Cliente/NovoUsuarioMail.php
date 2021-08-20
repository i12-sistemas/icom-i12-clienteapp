<?php

namespace App\Mail\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Exception;
use App\Models\ClienteUsuario;

class NovoUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $usuario;
    public $password;

    public function __construct(ClienteUsuario $usuario, $password)
    {
        $this->usuario = $usuario;
        if(!$this->usuario)
        throw new Exception('Usuário não informado');

        $this->password = $password;
        if(!$this->password)
          throw new Exception('Senha não foi informada');
    }

    public function build()
    {
        $to = [
            'address' => $this->usuario->email,
            'name' => $this->usuario->nome
        ];
        return $this->to($this->usuario->email, $this->usuario->nome)
                    ->subject('Seu usuário de acesso ao painel do cliente :: ' . env('APP_NAME',''))
                    ->markdown('emails.cliente.novousuario')
                    ->with([
                        'usuario' =>  $this->usuario,
                        'password' =>  $this->password,
                    ]);

    }
}
