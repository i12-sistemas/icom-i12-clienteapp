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

use App\Models\ClienteUsuario;

use App\Jobs\Cliente\NovoUsuarioSendMailJob;


class ClienteUsuarioController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $clienteid = isset($request->clienteid) ? $request->clienteid : null;
        $showall = isset($request->showall) ? boolval($request->showall) : false;
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
        $dataset = ClienteUsuario::select(DB::raw('clienteusuario.*'))
                    ->with( 'created_usuario', 'updated_usuario')
                    ->whereRaw('if(?=1, true, clienteusuario.ativo=1)', [$showall])
                    ->when($find, function ($query, $find) {
                        return $query->Where('clienteusuario.celular', 'like', '%'.cleanDocMask($find).'%')
                            ->orWhere('clienteusuario.nome', 'like', '%'.$find.'%')
                            ->orWhere('clienteusuario.email', 'like', '%'.$find.'%');
                    })
                    ->when(isset($request->nome), function ($query) use ($request)  {
                        $s = trim(utf8_decode($request->nome));
                        return $query->Where('clienteusuario.nome', 'like', '%'. $s . '%');
                    })
                    ->when(isset($request->email), function ($query) use ($request)  {
                        $s = trim(utf8_decode($request->email));
                        return $query->Where('clienteusuario.email', 'like', '%'. $s . '%');
                    })
                    ->when(isset($request->celular), function ($query) use ($request)  {
                        $s = trim(cleanDocMask(utf8_decode($request->celular)));
                        return $query->Where('clienteusuario.celular', 'like', '%'. $s . '%');
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('clienteusuario.id', $ids);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->groupBy('clienteusuario.id')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->toObject();
        }
        $ret->data = $dados;
        $ret->collection = $dataset;
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

        $dataset = ClienteUsuario::find($find);
        if (!$dataset) {
            $dataset = ClienteUsuario::where('email', '=', $find)->first();
            if (!$dataset) {
                $dataset = ClienteUsuario::where('celular', '=', cleanDocMask($find))->first();
            }
            if (!$dataset) throw new Exception("Usuário não foi encontrado");
        }

        $ret->data = $dataset->toObject();
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
            'email' => ['required', 'min:1', 'max:255'],
            'nome' => ['required', 'min:1', 'max:60'],
            'clienteid' => ['required', 'exists:cliente,id'],
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
            $dataset = ClienteUsuario::find($id);
            if (!$dataset) throw new Exception("Usuário não foi encontrada");
        }

        $email = $request->email;
        if (!validEmail($email)) throw new Exception("E-mail inválido");

        $check = ClienteUsuario::where('email', '=', $email)
                        ->whereRaw('if(?>0, id != ?, true)', [$id, $id])
                        ->first();
        if ($check) throw new Exception('O e-mail informado esta sendo usado por outro usuário no cliente ' . $check->cliente->razaosocial );

        if ($request->has('celular')) {
            $celular = cleanDocMask(trim(utf8_decode($request->celular)));
            $check = ClienteUsuario::where('celular', '=', $celular)
                            ->whereRaw('if(?>0, id != ?, true)', [$id, $id])
                            ->first();
            if ($check) throw new Exception('O celular informado esta sendo usado por outro usuário no cliente ' . $check->cliente->razaosocial );
        }




      } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
          return $ret->toJson();
      }

      try {
          DB::beginTransaction();


          if ($action=='add') {
              $dataset = new ClienteUsuario();
              $dataset->created_usuarioid = $usuario->id;
              $request->ativo = 1;
              $senha = createRandomVal(6);
              $dataset->senha = bcrypt($senha);
              \Log::debug($senha);
          }
          $dataset->updated_usuarioid = $usuario->id;
          $dataset->clienteid = $request->clienteid;
          $dataset->nome = $request->nome;
          $dataset->email = $email;
          if ($request->has('celular')) $dataset->celular = $celular;
          if ($request->has('ativo')) $dataset->ativo = $request->ativo;
          $dataset->save();

          DB::commit();

          $ret->id = $dataset->id;
          $ret->data = $dataset->toObject();
          $ret->msg = $action;
          $ret->ok = true;


      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }


    public function teste(Request $request)
    {
      $ret = new RetApiController;
      try {

        $find = isset($request->id) ? $request->id : null;
        if (!$find) throw new Exception("Nenhum id informado");

        $dataset = ClienteUsuario::find($find);
        if (!$dataset) {
            $dataset = ClienteUsuario::where('email', '=', $find)->first();
            if (!$dataset) {
                $dataset = ClienteUsuario::where('celular', '=', cleanDocMask($find))->first();
            }
            if (!$dataset) throw new Exception("Usuário não foi encontrado");
        }

        $this->dispatch(new NovoUsuarioSendMailJob($dataset, 'senhadeteste'));

        $ret->data = $dataset->toObject();
        $ret->ok = true;
      } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
          return $ret->toJson();
      }

      return $ret->toJson();
    }
}
