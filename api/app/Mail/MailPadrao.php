<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Exception;

use App\Models\Usuario;

class MailPadrao extends Mailable
{
    use Queueable, SerializesModels;

    protected $dados;
    public $to;
    public $copiato;
    protected $assunto;
    protected $mensagem;
    protected $anexos;
    protected $remetenteola;
    protected $usuario;

    public function __construct($dados, $usuario)
    {
        $this->dados = $dados;
        $this->usuario = $usuario;

        $to = isset($dados['to']) ? $dados['to'] : null;
        $this->to = [];
        foreach ($to as $item) {
            $this->to[] = [
                'name' => $item['nome'] == '' ? null : $item['nome'],
                'email' => $item['email'],
                'address' => $item['email']
            ];
        }
        if (count($this->to) <= 0) throw new Exception('Nenhum destinatário de e-mail informado');

        $this->remetenteola = '';
        if (count($this->to) == 1) {
            if (array_key_exists('name', $this->to[0])) {
                if ($this->to[0]['name'] !== '') $this->remetenteola = $this->to[0]['name'];
            }
        }

        $cc = isset($dados['cc']) ? $dados['cc'] : null;
        $this->copiato = null;
        if ($cc) {
            $this->copiato = [];
            foreach ($cc as $item) {
                $this->copiato[] = [
                    'name' => $item['nome'] == '' ? null : $item['nome'],
                    'email' => $item['email'],
                    'address' => $item['email']
                ];
            }
            if (count($this->copiato) <= 0) $this->copiato = null;
        }


        $anexos = isset($dados['anexos']) ? $dados['anexos'] : null;
        $this->anexos = null;
        if ($anexos) {
            $this->anexos = [];
            foreach ($anexos as $item) {
                $this->attachFromStorageDisk('public', $item);
                $this->anexos[] = $item;
            }
            if (count($this->anexos) <= 0) $this->anexos = null;
        }

        $this->assunto = array_key_exists('assunto', $dados) ? $dados['assunto'] : null;
        if (!$this->assunto) throw new Exception('Nenhum assunto informado');
        if (trim($this->assunto) === '') throw new Exception('Nenhum assunto informado');

        $this->mensagem = array_key_exists('mensagem', $dados) ? $dados['mensagem'] : null;
    }

    public function build()
    {
        if ($this->copiato) $this->cc($this->copiato);
        $dados = [
            'anexos'          =>  $this->anexos,
            'remetenteola'    =>  $this->remetenteola,
        ];
        $replyOK = false;
        if ($this->usuario) {
            if ($this->usuario->email !== '') {
                $this->replyTo($this->usuario->email, $this->usuario->nome);
                $replyOK = true;
            }
            $dados['usuario'] = $this->usuario;
        }

        if ($this->mensagem ? $this->mensagem != '' : false)  $dados['mensagem'] = $this->mensagem;

        $dados['replyOK'] = $replyOK;
        return $this->to($this->to)
                    ->subject($this->dados['assunto'])
                    ->markdown('emails.mailpadrao')
                    ->with($dados);
    }
}
