<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\MailPadrao;
use App\Models\Usuario;
use Mail;

class SendMailPadraoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dados;
    protected $usuario;

    public function __construct($dados, $usuario)
    {
        $this->dados = $dados;
        $this->usuario = $usuario;
    }


    public function handle()
    {
        $email = new MailPadrao($this->dados, $this->usuario);
        Mail::send($email);
    }
}
