<?php

namespace App\Console\Commands\ColetasNotas;

use Illuminate\Console\Command;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\ColetasNotaXMLToken;
use App\Jobs\ColetasNotas\EmailTokenXMLPendentePorUsuarioJob;

class LembreteNotasSemXMLCommand extends Command
{
    protected $signature = 'coletanota:lembretenotassemxml';
    protected $description = 'Envia e-mail de lembrete de notas sem XML ao cliente';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
          $sql = "select coletas_nota.remetentecnpj as cnpj, cliente.fantasia,
          ifnull(group_concat(distinct emailcliente.email), 'contato@i12.com.br') as email
          from coletas_nota
          left join coletas_nota_xml_token on coletas_nota_xml_token.cnpj=coletas_nota.remetentecnpj
                and coletas_nota_xml_token.expire_at > now()
                and coletas_nota_xml_token.origem = '2'
          left join coletas_nota_xml on coletas_nota_xml.chave=coletas_nota.notachave
          left join cliente on coletas_nota.remetentecnpj=cliente.cnpj
              left join
                  (select emails.*, cliente_email.clienteid
                      from emails_tags
                      inner join emails on emails.id=emails_tags.emailid
                      inner join cliente_email on cliente_email.emailid=emails.id
                      where emails_tags.tag='nfe') as emailcliente on emailcliente.clienteid=cliente.id
          where coletas_nota.baixanfestatus=2 and coletas_nota.idcoleta is null and coletas_nota_xml.id is null
          and coletas_nota.coletaavulsaignorada=0 and coletas_nota.baixanfetentativas>=2
          and date(coletas_nota.dhlocal_created_at)>=date_add(now(), interval -5 day)
          and coletas_nota_xml_token.token is null
          group by coletas_nota.remetentecnpj
          order by coletas_nota.dhlocal_created_at desc";

          $dataset = \DB::select( DB::raw($sql) );

          if (!$dataset) throw new Exception("Nenhuma nota encontrada");
          if (count($dataset)<=0) throw new Exception("Nenhuma nota encontrada");

          foreach ($dataset as $row) {


            try {
                DB::beginTransaction();


                $sql = "select coletas_nota.notanumero, coletas_nota.notaserie, coletas_nota.notachave
                from coletas_nota
                left join coletas_nota_xml on coletas_nota_xml.chave=coletas_nota.notachave
                where coletas_nota.baixanfestatus=2 and coletas_nota.idcoleta is null and coletas_nota_xml.id is null
                and coletas_nota.coletaavulsaignorada=0 and coletas_nota.baixanfetentativas>=2
                and date(coletas_nota.dhlocal_created_at)>=date_add(now(), interval -5 day)
                and coletas_nota.remetentecnpj=?
                order by date(coletas_nota.dhlocal_created_at) asc, coletas_nota.notanumero, coletas_nota.notaserie";

                $qry = \DB::select( DB::raw($sql), [$row->cnpj]);
                $listanotas = [];
                if ($qry) {
                    foreach ($qry as $nota) {
                        $listanotas[] = ['numero' => $nota->notanumero, 'serie' => $nota->notaserie, 'chave' => $nota->notachave];
                    }
                }
                if (count($listanotas) === 0) $listanotas = null;

                $emailA = explode(',', $row->email);
                $to = [];
                foreach ($emailA as $email) {
                    if (validEmail($email))
                    $to[] = [ 'email' => $email ];
                }
                if ($to ? count($to) > 0 : false) {
                    $link = new ColetasNotaXMLToken;
                    $link->origem = '2';
                    $link->notas = $listanotas ? json_encode($listanotas) : null;
                    $link->cnpj = $row->cnpj;
                    $link->chave = $to[0]["email"];
                    $link->tipo = 'email';
                    $link->expire_at = Carbon::now()->addHours(12);
                    $link->created_at = Carbon::now();
                    $codenumber = rand(10000000 , 99999999);
                    $link->token = md5($link->created_at->format('Ymdhis') . $link->cnpj . $link->chave . $link->tipo . $codenumber);
                    $link->to = json_encode($to);
                    $link->assunto = 'LEMBRETE ::  Solicitação de envio de arquivo XML :: CNPJ: ' . $row->cnpj . ' - ' . utf8_encode($row->fantasia);
                    $link->save();

                    DB::commit();

                    dispatch(new EmailTokenXMLPendentePorUsuarioJob($link));
                } else {
                    $this->info('Ignorado por falta de e-mail valido');
                }

            } catch (\Throwable $th) {
                DB::rollBack();
                $this->info($th->getMessage());
            }
          }
        } catch (\Throwable $th) {
            $this->info($th->getMessage());
        }
        $this->info('THE END');
        return 0;
    }
}
