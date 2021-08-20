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

use App\Models\Motorista;

class MotoristaController extends Controller
{

  public function list(Request $request)
  {
    $ret = new RetApiController;
    try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $showall = isset($request->showall) ? boolval($request->showall) : false;
        $resumedata = isset($request->resumedata) ? boolval($request->resumedata) : false;
        $ids = null;
        if (isset($request->ids)) {
        $ids = explode(",", $request->ids);
        if (!is_array($ids)) $ids[] = $ids;
        $ids = count($ids) > 0 ? $ids : null;
        }
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $motoristas = Motorista::select(DB::raw('motorista.*'))
            ->leftJoin('veiculo', 'motorista.veiculoid', '=', 'veiculo.id')
            ->whereRaw('if(?=1, true, motorista.ativo=1)', [$showall])
            ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                return $query->where(function($query2) use ($find) {
                    return $query2->Where('motorista.cpf', 'like', '%'.cleanDocMask($find).'%')
                        ->orWhere('motorista.fone', 'like', '%'.$find.'%')
                        ->orWhere('motorista.username', 'like', '%'.$find.'%')
                        ->orWhere('motorista.gerenciamentooutros', 'like', '%'.$find.'%')
                        ->orWhere('motorista.antt', 'like', '%'.$find.'%')
                        ->orWhere('motorista.nome', 'like', '%'.$find.'%')
                        ->orWhere('motorista.antt', 'like', '%'.$find.'%')
                        ->orWhere('motorista.apelido', 'like', '%'.$find.'%')
                        ->orWhere('veiculo.placa', 'like', '%'.cleanDocMask($find).'%')
                        ;
                });
            })
            ->when($request->ids, function ($query) use ($ids) {
                return $query->whereIn('motorista.id', $ids);
            })
            ->orderBy('motorista.nome')
            ->orderby('motorista.apelido')
            ->groupBy('motorista.id')
            ->paginate($perpage);
        $dados = [];
        foreach ($motoristas as $motorista) {
            if ($resumedata) {
              $dados[] = $motorista->exportsmall();
            } else {
              $dados[] = $motorista->toObjectList();
            }
        }
        $ret->data = $dados;
        $ret->collection = $motoristas;
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

        $motorista = Motorista::find($find);
        if (!$motorista) throw new Exception("Mptorista não foi encontrado");

        $ret->data = $motorista->toObject(False);
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
            'nome' => ['required', 'min:1', 'max:60'],
            'gerenciamento' => ['required', 'integer', 'min:0', 'max:3'],
            'apelido' => ['max:60'],
            'fone' => ['max:20'],
            'cidadeid' => ['required', 'exists:cidades,id'],
            'veiculoid' => ['exists:veiculo,id'],
            'cpf' => ['string', 'required' ],
            'username' => [
            'string',
            Rule::unique('motorista')->ignore(isset($request->id) ? intVal($request->id) : 0),
            ]
        ];
        $messages = [
        'size' => 'O campo :attribute, deverá ter :max caracteres.',
        'integer' => 'O conteudo do campo :attribute deverá ser um número inteiro.',
        'cpf.unique' => 'O conteudo do campo :attribute já foi cadastrado e está ativo.',
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

        if (isset($request->gerenciamento)) {
            if ((intVal($request->gerenciamento) == 3) && ((isset($request->gerenciamentooutros) ? $request->gerenciamentooutros : '') === ''))
            throw new Exception('Para o Gerenciamento de riscos "Outros", informe o campo "Outros"');
        }


        $id = isset($request->id) ? intVal($request->id) : 0;
        $action =  $id>0 ? 'update' : 'add';

        $checkcpf = Motorista::where('cpf', '=', $request->cpf)
                        ->where('ativo', '!=', 0)
                        ->whereRaw('if(?>0, id != ?, true)', [$id, $id])
                        ->whereRaw('if(? = 1, true, false)', [$request->ativo])
                        ->count();
        if ($checkcpf > 0 ) throw new Exception('Existe outro cadastro ativo com o mesmo CPF');




        if ($action=='update') {
        $motorista = Motorista::find($id);
        if (!$motorista) throw new Exception("Motorista não foi encontrada");
        }

    } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
    }

    try {
        DB::beginTransaction();


        if ($action=='add') {
        $motorista = new Motorista();
        $motorista->created_usuarioid = $usuario->id;
        }
        $motorista->nome = $request->nome;
        $motorista->apelido = $request->apelido;
        $motorista->fone = $request->fone;
        $motorista->ativo = $request->ativo;
        if (isset($request->senha)) $motorista->pwd = $request->senha;
        if (isset($request->cpf)) $motorista->cpf = $request->cpf;
        if (isset($request->gerenciamentooutros)) $motorista->gerenciamentooutros = $request->gerenciamentooutros;
        if (isset($request->gerenciamento)) $motorista->gerenciamento = $request->gerenciamento;
        if (isset($request->antt)) $motorista->antt = $request->antt;
        if (isset($request->salario)) $motorista->salario = $request->salario;
        if (isset($request->cnhvencimento)) $motorista->cnhvencimento = $request->cnhvencimento;
        if (isset($request->moppvencimento)) $motorista->moppvencimento = $request->moppvencimento;
        if (isset($request->habilitado)) $motorista->habilitado = $request->habilitado;
        if (isset($request->username)) $motorista->username = $request->username;
        if (isset($request->cidadeid)) $motorista->cidadeid = $request->cidadeid;
        $motorista->veiculoid =  $request->has('veiculoid') ? $request->veiculoid : null;

        $motorista->updated_usuarioid = $usuario->id;
        $motorista->save();

        DB::commit();

        $ret->id = $motorista->id;
        $ret->data = $motorista->toObject(false);
        $ret->msg = $action;
        $ret->ok = true;


    } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
    }


    return $ret->toJson();
  }


  public function delete(Request $request, $id)
  {
  $ret = new RetApiController;
  try {
    $find = isset($id) ? intVal($id) : 0;
    if (!($find>0)) throw new Exception("Nenhum id informado");

    $motorista = Motorista::find($id);
    if (!$motorista) throw new Exception("Motorista não foi encontrado");

    // $regiaocount = $cidade->regioes->count();
    // if ($regiaocount>0)
    //     throw new Exception($regiaocount==1 ? 'Existe uma região associada' :  "Existem " . $regiaocount . " regiões associadas");

  } catch (\Throwable $th) {
    $ret->msg = $th->getMessage();
    return $ret->toJson();
  }


  try {
    DB::beginTransaction();

    $del = $motorista->delete();

    DB::commit();

    $ret->msg = 'Motorista ' . $motorista->nome . ' foi excluído!';
    $ret->ok = true;
  } catch (\Throwable $th) {
    DB::rollBack();
    $ret->msg = $th->getMessage();
  }

  return $ret->toJson();
  }

}
