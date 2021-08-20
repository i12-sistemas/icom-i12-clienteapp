<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use PDF;

use App\Http\Controllers\RetApiController;

use App\Enums\OrcamentoSituacaoType;
use App\Enums\ColetasSituacaoType;
use App\Enums\ColetasEncerramentoTipoType;

use App\Models\Cliente;
use App\Models\Orcamento;
use App\Models\OrcamentoItens;
use App\Models\Coletas;
use App\Models\ColetasItens;
use App\Models\Usuario;

class OrcamentosController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'orcamento.dhcoleta';
        $descending = isset($request->descending) ? $request->descending : 'asc';
        $dhcoletai = isset($request->dhcoletai) ? $request->dhcoletai : null;
        $dhcoletaf = isset($request->dhcoletaf) ? $request->dhcoletaf : null;
        $createdati = isset($request->createdati) ? $request->createdati : null;
        $createdatf = isset($request->createdatf) ? $request->createdatf : null;
        $vlrfretei = isset($request->vlrfretei) ? $request->vlrfretei : null;
        $vlrfretef = isset($request->vlrfretef) ? $request->vlrfretef : null;
        $tomador = isset($request->tomador) ? $request->tomador : null;
        $produtosperigosos = isset($request->produtosperigosos) ? $request->produtosperigosos : null;
        $cargaurgente = isset($request->cargaurgente) ? $request->cargaurgente : null;
        $veiculoexclusico = isset($request->veiculoexclusico) ? $request->veiculoexclusico : null;
        $pesoi = isset($request->pesoi) ? floatval($request->pesoi) : null;
        $pesof = isset($request->pesof) ? floatval($request->pesof) : null;
        $qtdei = isset($request->qtdei) ? floatval($request->qtdei) : null;
        $qtdef = isset($request->qtdef) ? floatval($request->qtdef) : null;

        $clienteorigemstr = isset($request->clienteorigemstr) ? $request->clienteorigemstr : null;
        $clientedestinostr = isset($request->clientedestinostr) ? $request->clientedestinostr : null;
        $created_usuariostr = isset($request->created_usuariostr) ? $request->created_usuariostr : null;

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $situacao = null;
        if (isset($request->situacao)) {
            $situacao = explode(",", $request->situacao);
            if (!is_array($situacao)) $situacao[] = $situacao;
            $situacao = count($situacao) > 0 ? $situacao : null;
        }

        $created_usuario = null;
        if (isset($request->created_usuario)) {
            $created_usuario = explode(",", $request->created_usuario);
            if (!is_array($created_usuario)) $created_usuario[] = $created_usuario;
            $created_usuario = count($created_usuario) > 0 ? $created_usuario : null;
        }

        $clienteorigem = null;
        if (isset($request->clienteorigem)) {
            $clienteorigem = explode(",", $request->clienteorigem);
            if (!is_array($clienteorigem)) $clienteorigem[] = $clienteorigem;
            $clienteorigem = count($clienteorigem) > 0 ? $clienteorigem : null;
        }

        $clientedestino = null;
        if (isset($request->clientedestino)) {
            $clientedestino = explode(",", $request->clientedestino);
            if (!is_array($clientedestino)) $clientedestino[] = $clientedestino;
            $clientedestino = count($clientedestino) > 0 ? $clientedestino : null;
        }

        // se existir numero, cancela outros filtros
        $numero = isset($request->numero) ? intVal($request->numero) : null;
        if ($numero) {
            if (!($numero>0)) $numero = null;

            if ($numero>0) {
                $dhcoletaf = null;
                $dhcoletai = null;
                $situacao = null;
                $find  = null;
            }
        } else {
            if ($find != '') {
                $n = intval($find);
                if ($n > 0) $numero = $n;
            }
        }

        // se existir numero, cancela outros filtros
        $coletanumero = isset($request->coletanumero) ? intVal($request->coletanumero) : null;
        if ($coletanumero) {
            if (!($coletanumero>0)) $coletanumero = null;

            if ($coletanumero>0) {
                $dhcoletaf = null;
                $dhcoletai = null;
                $situacao = null;
                $find  = null;
            }
        } else {
            if ($find != '') {
                $n = intval($find);
                if ($n > 0) $coletanumero = $n;
            }
        }

        $orderby = null;
        $descending = true;
        $sortby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'createdat') {
                    $lKey = 'orcamento.created_at';
                } else  if ($key == 'created_usuario') {
                    $lKey = 'trim(created_usuario.nome)';
                } else  if ($key == 'clienteorigem') {
                    $lKey = 'trim(clienteorigem.razaosocial)';
                } else if ($key == 'clientedestino') {
                    $lKey = 'trim(clientedestino.razaosocial)';
                } else if ($key == 'veiculo') {
                    $lKey = 'trim(veiculo.placa)';
                } else if ($key == 'cidadedestino') {
                    $lKey = 'concat(cidadedestino.cidade,cidadedestino.uf)';
                } else if ($key == 'cidadeorigem') {
                    $lKey = 'concat(cidadeorigem.cidade,cidadeorigem.uf)';
                } else {
                    $lKey = 'orcamento.' . $key;

                }
                $orderbynew[$lKey] = strtoupper($value);
                $descending = strtoupper($value) === 'DESC';
                $sortby = $key;
            }
            if (count($orderbynew) > 0) {
                $orderby = $orderbynew;
            } else {
                $orderby = null;
            }
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $orcamentos = Orcamento::select(DB::raw('orcamento.*'))
                    // ->leftJoin('coletas', 'orcamento.coletaid', '=', 'coletas.id')
                    ->leftJoin('usuario as created_usuario', 'orcamento.created_usuarioid', '=', 'created_usuario.id')
                    ->leftJoin('cliente as clienteorigem', 'orcamento.origemclienteid', '=', 'clienteorigem.id')
                    ->leftJoin('cliente as clientedestino', 'orcamento.destinoclienteid', '=', 'clientedestino.id')
                    ->leftJoin('cidades as cidadecoleta', 'orcamento.endcoleta_cidadeid', '=', 'cidadecoleta.id')
                    ->leftJoin('motorista', 'orcamento.motoristaid', '=', 'motorista.id')
                    ->with( 'motorista', 'created_usuario', 'updated_usuario', 'updatedstatus_usuario', 'clienteorigem', 'clientedestino', 'coletacidade', 'coletaregiao' )
                    ->when($find, function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        return $query2->Where('orcamento.gestaocliente_itenscomprador', 'like', $find.'%')
                                ->orWhere('orcamento.gestaocliente_comprador', 'like', $find.'%')

                                ->orWhere('created_usuario.nome', 'like', $find.'%')

                                ->orWhere('orcamento.contatonome', 'like', $find.'%')
                                ->orWhere('orcamento.contatoemail', 'like', $find.'%')
                                ->orWhere('orcamento.obscoleta', 'like', $find.'%')
                                ->orWhere('orcamento.obsorcamento', 'like', $find.'%')
                                ->orWhere('orcamento.endcoleta_cep', 'like', $find.'%')

                                ->orWhere('cidadecoleta.cidade', 'like', $find.'%')
                                ->orWhere('cidadecoleta.estado', 'like', $find.'%')
                                ->orWhere('cidadecoleta.uf', 'like', $find.'%')

                                ->orWhere('motorista.nome', 'like', $find.'%')
                                ->orWhere('motorista.apelido', 'like', $find.'%')

                                ->orWhere('clienteorigem.razaosocial', 'like', $find.'%')
                                ->orWhere('clienteorigem.fantasia', 'like', $find.'%')
                                ->orWhere('clienteorigem.cnpj', 'like', $find.'%')
                                ->orWhere('clientedestino.razaosocial', 'like', $find.'%')
                                ->orWhere('clientedestino.fantasia', 'like', $find.'%')
                                ->orWhere('clientedestino.cnpj', 'like', $find.'%')
                                ;
                      });
                    })
                    ->when($numero, function ($query, $numero) {
                        return $query->Where('orcamento.id', $numero);
                    })
                    ->when($coletanumero, function ($query, $coletanumero) {
                        return $query->Where('orcamento.coletaid', $coletanumero);
                    })
                    ->when($tomador, function ($query, $tomador) {
                        return $query->Where('orcamento.tomador', $tomador);
                    })
                    ->when(isset($request->situacao) && ($situacao != null), function ($query, $t) use ($situacao) {
                        return $query->WhereIn('orcamento.situacao', $situacao);
                    })
                    ->when(isset($request->produtosperigosos), function ($query, $t) use ($produtosperigosos) {
                        return $query->Where('orcamento.produtosperigosos', '=', toBool($produtosperigosos) ? 1 : 0);
                    })
                    ->when(isset($request->cargaurgente), function ($query, $t) use ($cargaurgente) {
                        return $query->Where('orcamento.cargaurgente', '=', toBool($cargaurgente) ? 1 : 0);
                    })
                    ->when(isset($request->veiculoexclusico), function ($query, $t) use ($veiculoexclusico) {
                        return $query->Where('orcamento.veiculoexclusico', '=', toBool($veiculoexclusico) ? 1 : 0);
                    })
                    ->when(isset($request->clientedestino) && ($clientedestino != null), function ($query, $t) use ($clientedestino) {
                        return $query->WhereIn('orcamento.destinoclienteid', $clientedestino);
                    })
                    ->when(isset($request->clienteorigem) && ($clienteorigem != null), function ($query, $t) use ($clienteorigem) {
                        return $query->WhereIn('orcamento.origemclienteid', $clienteorigem);
                    })
                    ->when(isset($request->created_usuario) && ($created_usuario != null), function ($query, $t) use ($created_usuario) {
                        return $query->WhereIn('orcamento.created_usuarioid', $created_usuario);
                    })
                    ->when($request->dhcoletai, function ($query) use ($dhcoletai) {
                        return $query->Where(DB::Raw('date(orcamento.dhcoleta)'), '>=', $dhcoletai);
                    })
                    ->when($request->dhcoletaf, function ($query) use ($dhcoletaf) {
                        return $query->Where(DB::Raw('date(orcamento.dhcoleta)'), '<=', $dhcoletaf);
                    })
                    ->when($request->createdati, function ($query) use ($createdati) {
                        return $query->Where(DB::Raw('date(orcamento.created_at)'), '>=', $createdati);
                    })
                    ->when($request->createdatf, function ($query) use ($createdatf) {
                        return $query->Where(DB::Raw('date(orcamento.created_at)'), '<=', $createdatf);
                    })
                    ->when($request->vlrfretei, function ($query) use ($vlrfretei) {
                        return $query->Where(DB::Raw('orcamento.vlrfrete'), '>=', $vlrfretei);
                    })
                    ->when($request->vlrfretef, function ($query) use ($vlrfretef) {
                        return $query->Where(DB::Raw('orcamento.vlrfrete'), '<=', $vlrfretef);
                    })
                    ->when(isset($request->qtdei), function ($query) use ($qtdei) {
                        return $query->Where('orcamento.qtde', '>=', $qtdei);
                    })
                    ->when(isset($request->qtdef), function ($query) use ($qtdef) {
                        return $query->Where('orcamento.qtde', '<=', $qtdef);
                    })
                    ->when(isset($request->pesoi), function ($query) use ($pesoi) {
                        return $query->Where('orcamento.peso', '>=', $pesoi);
                    })
                    ->when(isset($request->pesof), function ($query) use ($pesof) {
                        return $query->Where('orcamento.peso', '<=', $pesof);
                    })

                    ->when(isset($request->clienteorigemstr) && ($clienteorigemstr ? $clienteorigemstr !== '' : false), function ($query) use ($clienteorigemstr)  {
                        return $query->where(function($query2) use ($clienteorigemstr) {
                            return $query2->where('clienteorigem.razaosocial', 'like', '%'.$clienteorigemstr.'%')
                                ->orWhere('clienteorigem.fantasia', 'like', '%'.$clienteorigemstr.'%');
                        });
                    })
                    ->when(isset($request->clientedestinostr) && ($clientedestinostr ? $clientedestinostr !== '' : false), function ($query) use ($clientedestinostr)  {
                        return $query->where(function($query2) use ($clientedestinostr) {
                            return $query2->where('clientedestino.razaosocial', 'like', '%'.$clientedestinostr.'%')
                            ->orWhere('clientedestino.fantasia', 'like', '%'.$clientedestinostr.'%');
                        });
                    })

                    ->when(isset($request->created_usuariostr) && ($created_usuariostr ? $created_usuariostr !== '' : false), function ($query) use ($created_usuariostr)  {
                        return $query->where(function($query2) use ($created_usuariostr) {
                            return $query2->where('created_usuario.nome', 'like', '%'.$created_usuariostr.'%');
                        });
                    })

                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->paginate($perpage);
        $dados = [];
        foreach ($orcamentos as $orcamento) {
            $dados[] = $orcamento->export(false);
        }
        $ret->data = $dados;
        $ret->sortby = $sortby;
        $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
        $ret->collection = $orcamentos;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function orcamentoToColeta (Request $request, $idorcamento)
    {
        $ret = new RetApiController;
        try {
            $dataset = Orcamento::find($idorcamento);
            if (!$dataset) throw new Exception("Orçamento não foi encontrado");
            if ($dataset->coletaid > 0) throw new Exception("Orçamento já tem coleta associada");

            $params = [
                'id' => null,
                'origem' => 2, //1=interno direto, 2=interno orcamento, 3=painel do cliente, 4=Coleta avulsa aplicativo
                // chavenota: self.chavenota,
                'situacao' => ColetasSituacaoType::tcsLiberado,
                'dhcoleta' => $dataset->dhcoleta,
                // dhbaixa: self.dhbaixa,
                'contatonome' => $dataset->contatonome,
                'contatoemail' => $dataset->contatoemail,
                'peso' => $dataset->peso,
                'qtde' => $dataset->qtde,
                'especie' => $dataset->especie,
                'obs' => $dataset->obscoleta,

                'veiculoexclusico' => $dataset->veiculoexclusico,
                'cargaurgente' => $dataset->cargaurgente,
                'produtosperigosos' => $dataset->produtosperigosos,

                'endcoleta_logradouro' => $dataset->endcoleta_logradouro,
                'endcoleta_endereco' => $dataset->endcoleta_endereco,
                'endcoleta_numero' => $dataset->endcoleta_numero,
                'endcoleta_bairro' => $dataset->endcoleta_bairro,
                'endcoleta_cep' => $dataset->endcoleta_cep,
                'endcoleta_complemento' => $dataset->endcoleta_complemento,
                'endcoleta_cidadeid' => $dataset->endcoleta_cidadeid,

                'motoristaid' => $dataset->motoristaid,
                'clienteorigemid' => $dataset->origemclienteid,
                'clientedestinoid' => $dataset->destinoclienteid,

                'gestaocliente_comprador' => $dataset->gestaocliente_comprador,
                'gestaocliente_ordemcompra' => $dataset->gestaocliente_ordemcompra,
                'gestaocliente_itenscomprador' => $dataset->gestaocliente_itenscomprador
            ];

            $itensInsert = [];
            if ($dataset->itens) {
                foreach ($dataset->itens as $item) {
                    $data = [
                        'produtoid' => $item->produtoid,
                        'produtonome' => $item->produtodescricao,
                        'embalagem' => $item->embalagem,
                        'qtde' => $item->qtde,
                    ];
                    $itensInsert[] = [
                        'item' => $data,
                        'action' => 'insert'
                    ];
                }
            }
            if (count($itensInsert) > 0) $params['itens'] = $itensInsert;

            $request->merge($params);

            $cc = app()->make('App\Http\Controllers\api\v1\ColetasController');
            $retProcessa = app()->call([$cc, 'save'], []);
            $retProcessa = (object)$retProcessa->getOriginalContent();
            $ret->ok = $retProcessa->ok;
            $ret->id = $retProcessa->id;
            $ret->msg = $retProcessa->msg;
            $ret->data = $retProcessa->data;

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret;
        }
        return $ret;
    }


    public function orcamentoCancelaColeta (Request $request, $idorcamento)
    {
        $ret = new RetApiController;
        try {
            $dataset = Orcamento::find($idorcamento);
            if (!$dataset) throw new Exception("Orçamento não foi encontrada");
            if (!($dataset->coletaid > 0)) throw new Exception("Orçamento sem coleta associada");

            $params = [
                'justificativa' => 'Cancelamento através da reabertura do orçamento #' . $dataset->id,
                'encerramentotipo' => ColetasEncerramentoTipoType::tetReaberturaOrcamento
            ];

            $request->merge($params);

            $cc = app()->make('App\Http\Controllers\api\v1\ColetasController');
            $retProcessa = app()->call([$cc, 'cancelar'], [ 'id' => $dataset->coletaid ]);
            $retProcessa = (object)$retProcessa->getOriginalContent();
            $ret->ok = isset($retProcessa->ok) ? $retProcessa->ok : false;
            $ret->id = isset($retProcessa->id) ? $retProcessa->id : null;
            $ret->msg = isset($retProcessa->msg) ? $retProcessa->msg : '';
            $ret->data = isset($retProcessa->data) ? $retProcessa->data : null;

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret;
        }
        return $ret;
    }


    public function find(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $orcamento = Orcamento::find($find);
        if (!$orcamento) throw new Exception("Orçamento não foi encontrado");

        $ret->data = $orcamento->toObject(False);
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function save(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'dhcoleta' => ['required', 'date'],
            'peso' => ['required', 'min:0'],
            'qtde' => ['required', 'min:0'],
            'especie' => ['max:150'],
            'veiculoexclusico' => ['required', 'boolean'],
            'cargaurgente' => ['required', 'boolean'],
            'produtosperigosos' => ['required', 'boolean'],
            'clienteorigemid' => ['required', 'exists:cliente,id'],
            'clientedestinoid' => ['required', 'exists:cliente,id'],
            'endcoleta_cidadeid' => ['required', 'exists:cidades,id']
        ];
        $messages = [
            'size' => 'O campo :attribute, deverá ter :max caracteres.',
            'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
            'unique' => 'O conteudo do campo :attribute já foi cadastrado.',
            'required' => 'O conteudo do campo :attribute é obrigatório.',
            'email' => 'O conteudo do campo :attribute deve ser um e-mail valido.',
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

        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id>0 ? 'update' : 'add';

        if ($action=='update') {
            $orcamento = Orcamento::find($id);
            if (!$orcamento) throw new Exception("Orçamento não foi encontrado");

            if ($orcamento->situacao == OrcamentoSituacaoType::tosReprovado) {
                throw new Exception("Situação atual do orçamento não permite alteração - Situação: " . $orcamento->situacao . " - " . OrcamentoSituacaoType::getDescription($orcamento->situacao));
            }
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();



        if ($action=='add') {
            $orcamento = new Orcamento();
            $orcamento->created_usuarioid = $usuario->id;
            $orcamento->situacao = OrcamentoSituacaoType::tosEmAberto;
            $orcamento->updatedstatus_usuarioid = $usuario->id;
            $orcamento->updatedstatus_at = Carbon::now();
        }

        // altera dados da coleta somente se estiver em aberto
        if ($orcamento->situacao == OrcamentoSituacaoType::tosEmAberto) {
            $orcamento->origemclienteid = $request->clienteorigemid;
            $orcamento->destinoclienteid = $request->clientedestinoid;
            if (!isset($request->motoristaid)) {
                $orcamento->motoristaid = null;
            } else {
                $orcamento->motoristaid = $request->motoristaid;
            }

            $orcamento->dhcoleta = $request->dhcoleta;

            $orcamento->contatonome = $request->contatonome;
            $orcamento->contatoemail = $request->contatoemail;
            $orcamento->peso = $request->peso;
            $orcamento->especie = $request->especie;
            $orcamento->qtde = $request->qtde;
            $orcamento->obscoleta = $request->obscoleta;

            $orcamento->veiculoexclusico = $request->veiculoexclusico ? 1 : 0;
            $orcamento->cargaurgente = $request->cargaurgente ? 1 : 0;
            $orcamento->produtosperigosos = $request->produtosperigosos ? 1 : 0;

            $orcamento->gestaoclienteOrdemcompra = $request->gestaocliente_ordemcompra;
            $orcamento->gestaoclienteComprador = $request->gestaocliente_comprador;
            $orcamento->gestaoclienteItenscomprador = $request->gestaocliente_itenscomprador;

            $orcamento->endcoleta_logradouro = $request->endcoleta_logradouro;
            $orcamento->endcoleta_endereco = $request->endcoleta_endereco;
            $orcamento->endcoleta_numero = $request->endcoleta_numero;
            $orcamento->endcoleta_bairro = $request->endcoleta_bairro;
            $orcamento->endcoleta_cep = $request->endcoleta_cep;
            $orcamento->endcoleta_complemento = $request->endcoleta_complemento;
            $orcamento->endcoleta_cidadeid = $request->endcoleta_cidadeid;
        }

        $orcamento->obsorcamento = $request->obsorcamento;
        $orcamento->vlrfrete = isset($request->vlrfrete) ? $request->vlrfrete : 0;
        if (!isset($request->tomador) || $request->tomador == '') {
            $orcamento->tomador = null;
        } else {
            $orcamento->tomador = $request->tomador;
        }

        $orcamento->updated_usuarioid = $usuario->id;
        $orcamento->save();

        if ($orcamento->situacao == OrcamentoSituacaoType::tosEmAberto) {
            if (isset($request->itens)) {
                $actions = $request->itens;
                foreach ($actions as $elemento) {
                    $elemento  =(object)$elemento;
                    $elemento->item = (object)$elemento->item;
                    if ($elemento->action == 'delete') {
                        $del = OrcamentoItens::find($elemento->item->id)->delete();
                        if (!$del) throw new Exception("Item não foi excluído - " . $elemento->item->produtodescricao);
                    }
                    if ($elemento->action == 'update') {
                        $item = OrcamentoItens::find($elemento->item->id);
                        if ($item) {
                            if ((isset($elemento->item->produtoid) ? $elemento->item->produtoid : 0) > 0) {
                                $item->produtoid = $elemento->item->produtoid;
                                $item->produtodescricao = $item->produto->nome;
                            } else {
                                $item->produtoid = null;
                                $item->produtodescricao = $elemento->item->produtonome;
                            }
                            $item->qtde = $elemento->item->qtde;
                            $item->embalagem = $elemento->item->embalagem;
                            $item->updated_usuarioid = $usuario->id;
                            $ins = $item->save();
                            if (!$ins) throw new Exception("Item não foi atualizado - " . $item->produtodescricao);
                        }
                    }
                    if ($elemento->action == 'insert') {
                        $item = new OrcamentoItens();
                        if ((isset($elemento->item->produtoid) ? $elemento->item->produtoid : 0) > 0) {
                            $item->produtoid = $elemento->item->produtoid;
                            $item->produtodescricao = $item->produto->nome;
                        } else {
                            $item->produtoid = null;
                            $item->produtodescricao = $elemento->item->produtonome;
                        }
                        $item->qtde = $elemento->item->qtde;
                        $item->embalagem = $elemento->item->embalagem;
                        $item->created_usuarioid = $usuario->id;
                        $item->updated_usuarioid = $usuario->id;
                        $item->orcamentoid = $orcamento->id;
                        $ins = $item->save();
                        if (!$ins) throw new Exception("Item não foi inserido - " . $item->produtodescricao);
                    }
                }

            }
        }

        DB::commit();


        $ret->id = $orcamento->id;
        $ret->data = $orcamento->toObject(false);
        $ret->msg = $action;
        $ret->ok = true;


      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function changeSituacaoAprovado(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');


        $situacao = isset($request->situacao) ? $request->situacao : null;
        if (!$situacao) throw new Exception("Situação não informada");
        if ($situacao == '') throw new Exception("Situação não informada");

        $situacao = OrcamentoSituacaoType::fromValue((string) $situacao);

        if (!($id>0)) throw new Exception("Nenhum ID de orçamento informado");

        $orcamento = Orcamento::find($id);
        if (!$orcamento) throw new Exception("Orçamento não foi encontrado");

        if ($orcamento->situacao == OrcamentoSituacaoType::tosReprovado)
            throw new Exception("Este orçamento foi reprovado!");

        if (($orcamento->situacao == OrcamentoSituacaoType::tosAprovadoColetaLiberada) && ($orcamento->id > 0))
            throw new Exception("Este orçamento foi aprovado e a coleta #" . $orcamento->coletaid . " gerada!");

        $gerarcoleta = false;
        if (($situacao == OrcamentoSituacaoType::tosAprovadoColetaLiberada) && !($orcamento->coletaid > 0))
            $gerarcoleta = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($gerarcoleta) {
            $retAddColeta = self::orcamentoToColeta($request, $orcamento->id);
            if (!$retAddColeta->ok)
                throw new Exception('Erro ao incluir coleta - ' . $retAddColeta->msg);

            $orcamento->coletaid = $retAddColeta->id;
        }
        $orcamento->situacao = $situacao;
        $orcamento->updatedstatus_at = Carbon::now();
        $orcamento->updatedstatus_usuarioid = $usuario->id;
        $orcamento->updated_usuarioid = $usuario->id;
        $orcamento->save();

        DB::commit();

        $ret->id = $orcamento->id;
        $ret->data = $orcamento->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }

    public function changeSituacaoReprovado(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        if (!($id>0)) throw new Exception("Nenhum ID de orçamento informado");

        $motivo = isset($request->motivo) ? $request->motivo : null;
        if (!$motivo) throw new Exception("Motivo da reprovação não informado");
        if (strlen($motivo) < 5) throw new Exception("Motivo da reprovação deve ter no mínimo 5 caracteres");

        $orcamento = Orcamento::find($id);
        if (!$orcamento) throw new Exception("Orçamento não foi encontrado");

        if ($orcamento->situacao !== OrcamentoSituacaoType::tosEmAberto)
            throw new Exception("Situação atual do orçamento não permite reprovar!");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $orcamento->situacao = OrcamentoSituacaoType::tosReprovado;
        $orcamento->justsituacao = $motivo;
        $orcamento->updatedstatus_at = Carbon::now();
        $orcamento->updatedstatus_usuarioid = $usuario->id;
        $orcamento->updated_usuarioid = $usuario->id;
        $orcamento->save();

        DB::commit();

        $ret->id = $orcamento->id;
        $ret->data = $orcamento->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }

    public function undoSituacaoToAberto(Request $request, $id)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $orcamento = Orcamento::find($id);
        if (!$orcamento) throw new Exception("Orçamento não foi encontrado");

        if ($orcamento->situacao == OrcamentoSituacaoType::tosEmAberto)
        throw new Exception("Este orçamento ja está em aberto!");

        $cancelarcoleta = false;
        if ($orcamento->coletaid > 0) {
            if ($orcamento->coleta) {
                if (!(($orcamento->coleta->situacao !== ColetasSituacaoType::tcsBloqueado) || ($orcamento->coleta->situacao !== ColetasSituacaoType::tcsLiberado)))
                    throw new Exception("Status atual da coleta não permite o cancelamento!");

                $cancelarcoleta = true;
            }
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($cancelarcoleta) {
            $retAddColeta = self::orcamentoCancelaColeta($request, $orcamento->id);
            if (!$retAddColeta->ok)
                throw new Exception('Erro ao cancelar coleta - ' . $retAddColeta->msg);

            $orcamento->coletaid = null;
        }

        $orcamento->situacao = OrcamentoSituacaoType::tosEmAberto;
        $orcamento->updatedstatus_at = Carbon::now();
        $orcamento->updatedstatus_usuarioid = $usuario->id;
        $orcamento->updated_usuarioid = $usuario->id;
        $orcamento->save();

        DB::commit();

        $ret->id = $orcamento->id;
        $ret->data = $orcamento->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }


    public function printOrcamento (Request $request)
    {
        $ret = new RetApiController;
        try {
            $disk = Storage::disk('public');

            $ids = isset($request->ids) ? $request->ids : null;
            $ids = explode(",", $ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;


            if (!$ids) throw new Exception('Nenhum número de orçamento informado');

            $rows = Orcamento::whereIn('id', $ids)->get();
            if (!$rows) throw new Exception('Nenhum orçamento encontrado');
            if ($rows->isEmpty()) throw new Exception('Nenhuma orçamento encontrado com os dados fornecidos');

            $path = $disk->path('temp');
            if (!$disk->exists('temp')) $disk->makeDirectory('temp');

            $html = view('pdf.orcamentos.orcamento', compact('rows'))->render();
            // $config = [
            //     'title' => 'Impressão',
            //     'author'=> ENV('APP_NAME',''),
            //     'orientation' => 'P',
            //     'format' => 'A4',
            //     'margin_left' => 5,
            //     'margin_right' => 5,
            //     'margin_top' => 5,
            //     'margin_bottom' => 5,
            //     'margin_header' => 0,
            //     'margin_footer' => 0,
            // ];
            $pdf = PDF::loadHtml($html);
            // $pdf = PDF::loadHtml($html, $config);
            $filename = 'orcamento-' . md5($html) . '.pdf';

            $file = 'temp/' . $filename;

            if (!$disk->exists($file)) $disk->delete($file);
            $pdf->save($disk->path($file));

            if (!$disk->exists('temp/' . $filename))
                throw new Exception('Falha ao gerar PDF. Arquivo não foi encontrado no disco.');


            // return $disk->download('temp/' . $filename, $filename, [
            //     'Content-Type' => 'application/pdf',
            //     'Content-Disposition' => 'inline; filename="'.$filename.'"'
            // ]);

            $ret->ok = true;
            $ret->msg = $disk->url($file);
            return $ret->toJson();

        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
    }

}
