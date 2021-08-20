<?php

namespace App\Http\Controllers\api\v1\publico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\RetApiController;

use App\Models\Cliente;
use App\Models\ColetasNota;
use App\Models\ColetasNotaXML;
use App\Models\ColetasNotaXMLToken;

class ClienteXMLPendenteController extends Controller
{

    // processa notas da tabela coletas_notas
    public function xmlpendente(Request $request)
    {
        $ret = new RetApiController;
        try {

            $rules = [
                'token' => ['required', 'size:32', 'string'],
                recaptchaFieldName() => recaptchaRuleName()
            ];
            $messages = [
                'size' => 'O campo :attribute, deverá ter :max caracteres.',
                'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
                'unique' => 'O conteudo do campo :attribute já foi cadastrado.',
                'required' => 'O conteudo do campo :attribute é obrigatório.',
                'email' => 'O conteudo do campo :attribute deve ser um e-mail valido.',
                recaptchaFieldName() => 'Validação humana falhou',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                $msgs = [];
                $errors = $validator->errors();
                foreach ($errors->all() as $message) {
                    $msgs[] = $message;
                }
                $ret->data = $msgs;
                throw new Exception(join("; ", $msgs));
            }

            $token = isset($request->token) ? $request->token : null;
            if (!$token) throw new Exception('Token inválido (1).');
            if (strlen($token) !== 32) throw new Exception('Token inválido (2).');

            $requisicao = ColetasNotaXMLToken::where('token', '=', $token)->first();
            if (!$requisicao) throw new Exception('Token não encontrado');
            if ($requisicao->expirado) throw new Exception('Token expirado');

            // $pCNPJ = $cnpj ? cleanDocMask($cnpj) : null;
            // if (!$pCNPJ) throw new Exception('CNPJ inválido.');

            $cliente = Cliente::where('cnpj', '=', $requisicao->cnpj)->first();
            if ($cliente) {
                if ($cliente->ativo !== 1) throw new Exception('Cliente inativo! Se necessário entre em contato.');
            }

            $datacliente = [
                'cnpj' => $requisicao->cnpj,
                'razaosocial' => $cliente ? $cliente->razaosocial : 'Não identificado'
            ];

            $sql = "select coletas_nota.notanumero, coletas_nota.notaserie, coletas_nota.notachave, coletas_nota.dhlocal_created_at as dhultimo
                    from coletas_nota
                    left join coletas_nota_xml on coletas_nota_xml.chave=coletas_nota.notachave
                    where coletas_nota.baixanfestatus=2 and coletas_nota.idcoleta is null and coletas_nota_xml.id is null
                    and coletas_nota.coletaavulsaignorada=0 and coletas_nota.baixanfetentativas>=2
                    and date(coletas_nota.dhlocal_created_at)>=date_add(now(), interval -5 day)
                    and coletas_nota.remetentecnpj=?
                    order by date(coletas_nota.dhlocal_created_at) asc, coletas_nota.notanumero, coletas_nota.notaserie";

            $dataset = \DB::select( DB::raw($sql), [$requisicao->cnpj]);

            try {
                DB::beginTransaction();

                if ($dataset ? count($dataset) === 0 : true) {
                    $requisicao->expire_at = Carbon::now()->addMinutes(1);
                }
                $codenumber = rand(10000000 , 99999999);
                $requisicao->accesscode = bcrypt($requisicao->token . $requisicao->expire_at->format('Ymdhis') . $requisicao->cnpj . $codenumber . Carbon::now());
                $requisicao->access_at = Carbon::now();
                $requisicao->save();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                \Log::error('Erro ao salvar leitur no banco de dados - ' . $th->getMessage());
            }

            $ret->data = [
                'cliente' => $datacliente,
                'notas' => $dataset,
                'accesscode' => $requisicao->accesscode
            ];

            $ret->ok = true;
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();

    }

    public function xmlenvio(Request $request, $cnpj)
    {
        $ret = new RetApiController;
        try {
            $pCNPJ = $cnpj ? cleanDocMask($cnpj) : null;
            if (!$pCNPJ) throw new Exception('CNPJ inválido.');

            $notachave = isset($request->notachave) ? trim($request->notachave) : '';
            if ($notachave ? $notachave === '' : true) throw new Exception('CNPJ inválido.');


            $token = isset($request->token) ? trim($request->token) : '';
            if ($token ? $token === '' : true) throw new Exception('token inválido.');

            $accesscode = isset($request->accesscode) ? trim($request->accesscode) : '';
            if ($accesscode ? $accesscode === '' : true) throw new Exception('accesscode inválido.');

            $requisicao = ColetasNotaXMLToken::where('token', '=', $token)->first();
            if (!$requisicao) throw new Exception('Token não encontrado');
            if ($requisicao->expirado) throw new Exception('Token expirado');
            if ($requisicao->accesscode !== $accesscode) throw new Exception('Accesscode não confere. Atualize sua tela.');

            $arquivo = $request->file('file');
            if (!$arquivo) throw new Exception('Nenhum arquivo enviado');

            $md5 = md5_file($arquivo->getRealPath());
            $ext = \Str::lower(mime2ext($arquivo->getMimeType()));
            $size = $arquivo->getSize();

            $extallow = ['xml'];
            if (!in_array($ext, $extallow)) throw new Exception('Arquivo com extensão ' . $ext . ' não é permitido. Extensões permitidas ' . implode(', ', $extallow));

            $maxsizebytes = 614400;
            if ($size > $maxsizebytes) throw new Exception('Tamanho do arquivo ' . humanReadBytes($size) . ' excede o tamanho permitido. Tamanho máximo é de ' . humanReadBytes($maxsizebytes));

            $nota = ColetasNota::where('notachave', '=', $notachave)->first();
            if (!$nota) throw new Exception('Nenhum registro de nota encontrado com a chave informada.');
            if ($nota->baixanfestatus === 0) throw new Exception('Nota está em processamento.');
            if ($nota->baixanfestatus === 1) throw new Exception('Nota já foi processada.');

            // Transformando o conteúdo XML da variável $string em Objeto
            $dataxml = file_get_contents($arquivo);
            $xml = simplexml_load_string($dataxml);
            $chave = $xml->NFe->infNFe['Id']->__toString();
            $chave = str_replace('NFe', '', $chave) ;
            if (((intVal($xml->NFe->infNFe->ide->nNF) !== intVal($nota->notanumero)) && (intVal($xml->NFe->infNFe->ide->nNF) > 0))) throw new Exception('Arquivo errado - NF-e número: ' . $xml->NFe->infNFe->ide->nNF . ' - Correto: ' . $nota->notanumero);
            if ($chave !== $nota->notachave) throw new Exception('Arquivo com chave diferente da nota.');

            try {
                DB::beginTransaction();

                $row = new ColetasNotaXML;
                $row->chave = $chave;
                $row->created_at = Carbon::now();
                $row->fileblob = $dataxml;
                $row->filestorage = 'db';
                $row->filename = $chave . '.xml';
                $row->fileext = 'xml';
                $row->filesize = $size;
                $row->filemd5 = $md5;
                $row->save();

                $nota->baixanfestatus = 0;
                $nota->baixanfetentativas = 0;
                $nota->baixanfemsg = 'Arquivo enviado pelo cliente';
                $nota->baixanfedhproc = Carbon::now();
                $nota->save();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new Exception('Erro ao salvar no banco de dados - ' . $th->getMessage());
            }


            $ret->ok = true;
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();

        }
}
