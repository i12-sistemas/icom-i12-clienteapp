<?php

namespace App\Mail\ColetasNotas;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Exception;
use App\Models\ColetasNotaXMLToken;

class EmailTokenXMLPendentePorUsuarioMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $token;
    public $to;
    public $copiato;
    public $assunto;
    public $mensagem;

    public function __construct(ColetasNotaXMLToken $token)
    {
        $this->token = $token;

        if(!$this->token)
          throw new Exception('Token não informado');

        if($this->token->expirado)
          throw new Exception('Token já processado ou expirado');

        $toA = json_decode($this->token->to, true);
        $this->to = [];
        foreach ($toA as $to) {
            $this->to[] = [
                'name' => (isset($to['nome']) ? $to['nome'] !== '' : false) ? $to['nome'] : null,
                'email' => $to['email'],
                'address' => $to['email']
            ];
        }
        if (count($this->to) <= 0) throw new Exception('Nenhum destinatário de e-mail informado');

        $cc = json_decode($this->token->cc, true);
        $this->copiato = null;
        if ($cc) {
            $this->copiato = [];
            foreach ($cc as $item) {
                $this->copiato[] = [
                    'name' => (isset($item['nome']) ? $item['nome'] !== '' : false) ? $item['nome'] : null,
                    'email' => $item['email'],
                    'address' => $item['email']
                ];
            }
            if (count($this->copiato) <= 0) $this->copiato = null;
        }
        $this->assunto = $this->token->assunto !== '' ? $this->token->assunto : 'Solicitação de envio de arquivo XML';
        $this->mensagem = $this->token->mensagem !== '' ? $this->token->mensagem : null;
    }

    public function build()
    {

        if ($this->copiato) $this->cc($this->copiato);
        return $this->to($this->to)
                    ->subject($this->assunto)
                    ->markdown('emails.coletasnotas.tokenxmlpendenteporusuario')
                    ->with([
                        'token' =>  $this->token,
                        'mensagem' =>  $this->mensagem,
                    ]);

    }
}
