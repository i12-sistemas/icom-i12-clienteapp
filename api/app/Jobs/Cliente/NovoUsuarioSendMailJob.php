<?php

namespace App\Jobs\Cliente;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Mail;
use App\Mail\Cliente\NovoUsuarioMail;
use App\Models\ClienteUsuario;

class NovoUsuarioSendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $usuario;
    protected $password;

    public function __construct(ClienteUsuario $usuario, $password)
    {
        $this->usuario = $usuario;
        $this->password = $password;
    }

    public function handle()
    {
        $email = new NovoUsuarioMail($this->usuario, $this->password);
        Mail::send($email);
    }
}
