<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;

use App\Models\Cliente;
use App\Models\Emails;
use App\Models\Cidades;


class ClienteController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $razaosocial = isset($request->razaosocial) ? $request->razaosocial : null;
        $showall = isset($request->showall) ? boolval($request->showall) : false;
        $qtdeusuario = isset($request->qtdeusuario) ? intval($request->qtdeusuario) : false;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }
        $orderby = null;
        $sortby = 'ASC';
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'cidade') {
                    $lKey = 'concat(cidades.cidade,cidades.uf)';
                } else if ($key == 'qtdeusuario') {
                    $lKey = 'qtdeusuario';
                } else if ($key == 'ids') {
                    $lKey = 'cliente.id';
                } else {
                    $lKey = 'cliente.' . $key;

                }
                $sortby = mb_strtoupper($value);
                $orderbynew[$lKey] = $sortby;
            }
            $orderbynew['cidades.cidade'] = $sortby;
            $orderbynew['cliente.id'] = $sortby;
            if (count($orderbynew) > 0) {
                $orderby = $orderbynew;
            } else {
                $orderby = null;
            }
        }
        $perpage = isset($request->perpage) ? $request->perpage : 20;
        $clientes = Cliente::select(DB::raw('cliente.*'), DB::raw('count(distinct clienteusuario.id) as qtdeusuario'))
                    ->leftJoin('cidades', 'cliente.cidadeid', '=', 'cidades.id')
                    ->leftJoin('clienteusuario', 'cliente.id', '=', 'clienteusuario.clienteid')
                    ->with( 'cidade', 'created_usuario', 'updated_usuario', 'emails')
                    ->whereRaw('if(?=1, true, cliente.ativo=1)', [$showall])
                    ->when($find, function ($query, $find) {
                        return $query->Where('cliente.cnpj', 'like', '%'.cleanDocMask($find).'%')
                            ->orWhere('cliente.razaosocial', 'like', '%'.$find.'%')
                            ->orWhere('cliente.fantasia', 'like', '%'.$find.'%')
                            ->orWhere('cliente.fantasia_followup', 'like', '%'.$find.'%')
                            ->orWhere('cliente.fone1', 'like', '%'.$find.'%')
                            ->orWhere('cliente.fone2', 'like', '%'.$find.'%')
                            ->orWhere('cliente.obs', 'like', '%'.$find.'%')
                            ->orWhere('cliente.complemento', 'like', '%'.$find.'%')
                            ->orWhere('cliente.bairro', 'like', '%'. $find.'%')
                            ->orWhere('cliente.endereco', 'like', '%'.$find.'%')
                            ->orWhereRaw('concat(cliente.razaosocial,cidades.cidade) like ?', ['%'.$find.'%'])
                            ->orWhereRaw('concat(cliente.fantasia_followup,cidades.cidade) like ?', ['%'.$find.'%'])
                            ->orWhereRaw('concat(cliente.fantasia,cidades.cidade) like ?', ['%'.$find.'%']);
                    })
                    ->when(isset($request->razaosocial), function ($query) use ($request)  {
                        $s = trim(utf8_decode($request->razaosocial));
                        return $query->Where('cliente.razaosocial', 'like', '%'. $s . '%');
                    })
                    ->when(isset($request->fantasia), function ($query) use ($request)  {
                        $s = trim(utf8_decode($request->fantasia));
                        return $query->Where('cliente.fantasia', 'like', '%'. $s . '%');
                    })
                    ->when(isset($request->cnpj), function ($query) use ($request)  {
                        $s = trim(cleanDocMask(utf8_decode($request->cnpj)));
                        return $query->Where('cliente.cnpj', 'like', '%'. $s . '%');
                    })
                    ->when(isset($request->fone1), function ($query) use ($request)  {
                        $s = trim(cleanDocMask(utf8_decode($request->fone1)));
                        return $query->Where('cliente.fone1', 'like', '%'. $s . '%');
                    })
                    ->when(isset($request->fone2), function ($query) use ($request)  {
                        $s = trim(cleanDocMask(utf8_decode($request->fone2)));
                        return $query->Where('cliente.fone2', 'like', '%'. $s . '%');
                    })
                    ->when(isset($request->cidade), function ($query) use ($request)  {
                        return $query->where(function($query2) use ($request) {
                            $s = trim(utf8_decode($request->cidade));
                            return $query2->where('cidades.cidade', 'like', '%'.$s.'%')
                            ->orWhere('cidades.uf', 'like', '%'.$s.'%');
                        });
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('cliente.id', $ids);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->groupBy('cliente.id')
                    ->when($request->has('qtdeusuario') && ($qtdeusuario >= 0), function ($query) use ($qtdeusuario) {
                        return $query->havingRaw ('count(distinct clienteusuario.id)=?', [$qtdeusuario]);
                    })
                    ->paginate($perpage);
        $dados = [];
        foreach ($clientes as $cliente) {
            $dados[] = $cliente->toObject();
        }
        $ret->data = $dados;
        $ret->collection = $clientes;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function find(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $cliente = Cliente::find($find);
        if (!$cliente) {
            $cliente = Cliente::where('cnpj', '=', cleanDocMask($find))->first();
            if (!$cliente) throw new Exception("Cliente não foi encontrado");
        }

        $ret->data = $cliente->toObject(False);
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
            'razaosocial' => ['required', 'min:1', 'max:255'],
            'fantasia' => ['required', 'min:1', 'max:255'],
            'fantasia_followup' => ['min:1', 'max:255'],
            'fone1' => ['max:20'],
            'fone2' => ['max:20'],
            'cidadeid' => ['required', 'exists:cidades,id'],
            'cnpj' => [
              'string',
              Rule::unique('cliente')->ignore(isset($request->id) ? intVal($request->id) : 0),
            ]
        ];
        $messages = [
            'size' => 'O campo :attribute, deverá ter :max caracteres.',
            'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
            'unique' => 'O conteudo do campo :attribute já foi cadastrado.',
            'required' => 'O conteudo do campo :attribute é obrigatório.',
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
            $cliente = Cliente::find($id);
            if (!$cliente) throw new Exception("Cliente não foi encontrada");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $cliente = new Cliente();
            $cliente->created_usuarioid = $usuario->id;
        }
        $cliente->razaosocial = $request->razaosocial;
        $cliente->fantasia = $request->fantasia;
        $cliente->cnpj = $request->cnpj;
        $cliente->ativo = $request->ativo;

        $cliente->segqui_hr1_i = $request->segqui_hr1_i;
        $cliente->segqui_hr1_f = $request->segqui_hr1_f;
        $cliente->segqui_hr2_i = $request->segqui_hr2_i;
        $cliente->segqui_hr2_f = $request->segqui_hr2_f;


        $cliente->sex_hr1_i = $request->sex_hr1_i;
        $cliente->sex_hr1_f = $request->sex_hr1_f;
        $cliente->sex_hr2_i = $request->sex_hr2_i;
        $cliente->sex_hr2_f = $request->sex_hr2_f;

        $cliente->portaria_hr1_i = $request->portaria_hr1_i;
        $cliente->portaria_hr1_f = $request->portaria_hr1_f;
        $cliente->portaria_hr2_i = $request->portaria_hr2_i;
        $cliente->portaria_hr2_f = $request->portaria_hr2_f;


        if (isset($request->fone1)) $cliente->fone1 = $request->fone1;
        if (isset($request->fone2)) $cliente->fone2 = $request->fone2;
        if (isset($request->obs)) $cliente->obs = $request->obs;
        if (isset($request->fantasia_followup)) $cliente->fantasia_followup = $request->fantasia_followup;
        if (isset($request->cnpjmemo)) $cliente->cnpjmemo = $request->cnpjmemo;

        if (isset($request->logradouro)) $cliente->logradouro = $request->logradouro;
        if (isset($request->endereco)) $cliente->endereco = $request->endereco;
        if (isset($request->numero)) $cliente->numero = $request->numero;
        if (isset($request->bairro)) $cliente->bairro = $request->bairro;
        if (isset($request->cep)) $cliente->cep = $request->cep;
        if (isset($request->complemento)) $cliente->complemento = $request->complemento;
        if (isset($request->cidadeid)) $cliente->cidadeid = $request->cidadeid;

        $cliente->updated_usuarioid = $usuario->id;
        $cliente->save();

        if (isset($request->emails)) {
            $actions = $request->emails;
            foreach ($actions as $elemento) {
                $elemento  =(object)$elemento;
                $elemento->email  =(object)$elemento->email;
                if ($elemento->action == 'delete') {
                    $email = Emails::where('email', '=', $elemento->email->email)->first();
                    if ($email) {
                        $ins = DB::table('cliente_email')
                                ->where('emailid', '=', $email->id)
                                ->where('clienteid', '=', $cliente->id)
                                ->delete();
                        if (!$ins) throw new Exception("E-mail não foi excluído - " . $email->email);

                        if ($email->clientes->count() == 0) {
                            $ins = $email->delete();
                            if (!$ins) throw new Exception("E-mail não foi excluído - " . $email->email);
                        }

                    }
                }

                if (($elemento->action == 'update') || ($elemento->action == 'insert')) {
                    $email = Emails::where('email', '=', $elemento->email->email)->first();
                    if ($email) {
                        $email->nome = $elemento->email->nome ? $elemento->email->nome : '';

                        $tags = [];
                        foreach ($elemento->email->tags as $tag) {
                            $tags[] = [
                                'emailid'   =>  $email->id,
                                'tag'   =>  utf8_decode($tag),
                            ];
                        }
                        if  ($elemento->action == 'update') $email->tags()->delete();
                        $email->tags()->insertOrIgnore($tags);

                        $ins = $email->save();
                        if (!$ins) throw new Exception("E-mail não foi atualizado - " . $email->email);
                    } else {
                        $emailvalido = validEmail($elemento->email->email) ;
                        if (!$emailvalido)
                            throw new Exception("E-mail inválido - " . $elemento->email->email);

                        $email = new Emails();
                        $email->nome = $elemento->email->nome;
                        $email->email = $elemento->email->email;
                        $ins = $email->save();
                        if (!$ins) throw new Exception("E-mail não foi inserido - " . $email->email);
                    }
                    if  ($elemento->action == 'insert') {
                        $ins = DB::table('cliente_email')->insertOrIgnore([
                            ['emailid' => $email->id, 'clienteid' => $cliente->id]
                        ]);
                        if (!$ins) throw new Exception("E-mail não foi inserido - " . $email->email);
                    }
                }
            }

        }


        DB::commit();

        $ret->id = $cliente->id;
        $ret->data = $cliente->toObject(false);
        $ret->msg = $action;
        $ret->ok = true;


      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function addNovoComXML($pXML, $pTag = 'dest', $usuarioid = 0, $pObs = '')
    {
      $ret = new RetApiController;
      try {
        if (!$pXML) throw new Exception("XML vazio para leitura");
        if ($pTag === 'emit') {
          $xml = $pXML->NFe->infNFe->emit;
          $xmlEnder = $xml->enderEmit;
        } else {
          $xml = $pXML->NFe->infNFe->dest;
          $xmlEnder = $xml->enderDest;
        }

        $docCNPJCPF = cleanDocMask(isset($xml->CNPJ) ? $xml->CNPJ : $xml->CPF);
        if (strlen($docCNPJCPF)<11) throw new Exception("Documento da nota não foi encontrado");
        $cliente = Cliente::where('cnpj', '=', $docCNPJCPF)->first();
        if ($cliente) {
            $ret->id = $cliente->id;
            $ret->data = $cliente;
            $ret->ok = true;
            return $ret;
        }


    } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret;
    }

    try {
        DB::beginTransaction();


        $cliente = new Cliente();
        $cliente->created_usuarioid = $usuarioid;
        $cliente->updated_usuarioid = $usuarioid;

        $cliente->razaosocial = $xml->xNome;
        $cliente->fantasia =  $xml->xNome;
        $cliente->cnpj = $docCNPJCPF;
        $cliente->ativo = true;

        if (isset($xml->fone)) $cliente->fone1 = $request->fone;
        $cliente->obs = $pObs;

        if (isset($xmlEnder->xLgr)) $cliente->endereco = $xmlEnder->xLgr;
        if (isset($xmlEnder->nro)) $cliente->numero = $xmlEnder->nro;
        if (isset($xmlEnder->xBairro)) $cliente->bairro = $xmlEnder->xBairro;
        if (isset($xmlEnder->CEP)) $cliente->cep = cleanDocMask($xmlEnder->CEP);
        if (isset($xmlEnder->xCpl)) $cliente->complemento = $xmlEnder->xCpl;
        $cMun = $xmlEnder->cMun;
        $cidade = Cidades::where('codigo_ibge', '=', $cMun)->first();
        if (!$cidade) throw new Exception("Nenhuma cidade cadastrada com o código do IBGE número " . $cMun);
        $cliente->cidadeid = $cidade->id;

        $email = \Str::lower(isset($xml->email) ? $xml->email : '');
        $cliente->save();

        if (strlen($email) > 2) {
          $emailCad = Emails::where('email', '=', $email)->first();
          if (!$emailCad) {
            $emailCad = new Emails();
            $emailCad->email = $email;
            $emailCad->save();
          }
          $ins = DB::table('cliente_email')->insertOrIgnore([
              ['emailid' => $emailCad->id, 'clienteid' => $cliente->id]
          ]);
        }

        DB::commit();

        $ret->id = $cliente->id;
        $ret->data = $cliente;
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret;
    }


    public function delete(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $cliente = Cliente::find($id);
        if (!$cliente) throw new Exception("Cliente não foi encontrado");

        // $regiaocount = $cidade->regioes->count();
        // if ($regiaocount>0)
        //     throw new Exception($regiaocount==1 ? 'Existe uma região associada' :  "Existem " . $regiaocount . " regiões associadas");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $cliente->delete();

        DB::commit();

        $ret->msg = 'Cliente ' . $cliente->razaosocial . ' foi excluído!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }

}
