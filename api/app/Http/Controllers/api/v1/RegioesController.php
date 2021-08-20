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

use App\Models\Regiao;
use App\Models\Cidades;


class RegioesController extends Controller
{

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $regioes = Regiao::when($find, function ($query, $find) {
                        $findid = intVal($find);
                        return $query->Where('regiao', 'like', '%'.$find.'%')
                                    ->orWhere('id', 'like', $findid.'%');
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->orderBy('regiao')
                    ->paginate($perpage);

        $dados = [];
        foreach ($regioes as $regiao) {
            $dados[] = $regiao->toObject(true);
        }
        $ret->collection = $regioes;
        $ret->data = $dados;
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    // lista cidades de uma regiao
    public function cidadeslist(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $regiao = Regiao::find($find);
        if (!$regiao) throw new Exception("Região não foi encontrada");


        $dadoscidade = [];
        foreach ($regiao->cidades as $cidade) {
            $c = $cidade->toObject(true);
            $dadoscidade[] = $c;
        }

        $ret->data = $dadoscidade;
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

        $regiao = Regiao::find($find);
        if (!$regiao) throw new Exception("Região não foi encontrada");

        $ret->data = $regiao->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
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

        $regiao = Regiao::find($find);
        if (!$regiao) throw new Exception("Região não foi encontrada");

        // $ret->data = $regiao->toObject(True);
        // $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $regiao->delete();

        DB::commit();

        $ret->msg = 'Região ' . $regiao->regiao . ' foi excluída!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
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
            'regiao' => ['max:60',
                    'required',
                    Rule::unique('regiao')->ignore(isset($request->id) ? intVal($request->id) : 0),
                ],
            'sugerirmotorista' => ['boolean'],
        ];
        $messages = [
            'max' => 'O campo :attribute, deverá ter no máximo :max caracteres.',
            'boolean' => 'O conteudo do campo :attribute deverá ser um número falso ou verdadeiro.',
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
            $regiao = Regiao::find($id);
            if (!$regiao) throw new Exception("Região não foi encontrada");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
            $regiao = new Regiao();
            $regiao->created_usuarioid = $usuario->id;
        }
        $regiao->regiao = $request->regiao;
        $regiao->sugerirmotorista = $request->sugerirmotorista;
        $regiao->updated_usuarioid = $usuario->id;
        $regiao->save();

        DB::commit();

        $ret->id = $regiao->id;
        $ret->data = $regiao->toObject(false);
        $ret->msg = $action;
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function deleteCidades(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');


        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $list = isset($request->cidadesids) ? $request->cidadesids : null;
        if (!$list) throw new Exception("Nenhuma cidade informada");

        $cidadesids = [];
        foreach ($request->cidadesids as $value) {
          $n = intVal($value);
          if ($value>0) $cidadesids[] = $n;
        }

        if (count($cidadesids)==0) throw new Exception("Nenhuma cidade informada");

        $regiao = Regiao::find($find);
        if (!$regiao) throw new Exception("Região não foi encontrada");

        $cidades = Cidades::where('regiaoid', '=', $regiao->id)->whereIn('id', $cidadesids)->get();
        if (!$cidades) throw new Exception("Nenhuma cidade encontrada");


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {


        DB::beginTransaction();

        $update = Cidades::where('regiaoid', '=', $regiao->id)
                        ->whereIn('id', $cidadesids)
                        ->update([
                            'regiaoid' => null,
                            'updated_usuarioid' => $usuario->id
                            ]);
        if (!$update) throw new Exception("Nenhuma cidade alterada");


        DB::commit();

        $ret->msg = ($update==0 ? 'Nenhum registro alterado' : ($update == 1 ? 'Um registro alterado' : $update . ' alterados')) ;
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function addCidades(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');


        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $list = isset($request->cidadesids) ? $request->cidadesids : null;
        if (!$list) throw new Exception("Nenhuma cidade informada");


        $cidadesids = [];
        foreach ($request->cidadesids as $value) {
            $n = intVal($value);
            if ($value>0) $cidadesids[] = $n;
        }
        if (count($cidadesids)==0) throw new Exception("Nenhuma cidade informada");

        $cidades = Cidades::whereNull('regiaoid')->whereIn('id', $cidadesids)->get();
        if (!$cidades) throw new Exception("Nenhuma cidade encontrada");

        $nomecidade = '';
        if ($cidades->count() == 1) {
            $nomecidade = $cidades[0]->cidade . ' - ' . $cidades[0]->estado;
        }

        $regiao = Regiao::find($find);
        if (!$regiao) throw new Exception("Região não foi encontrada");


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        $update = Cidades::whereNull('regiaoid')->whereIn('id', $cidadesids)
                ->update([
                    'regiaoid' =>  $regiao->id,
                    'updated_usuarioid' => $usuario->id
                ]);
        if (!$update) throw new Exception("Nenhuma cidade alterada");

        DB::commit();

        $ret->msg = ($update==0 ? 'Nenhuma cidade alterada' : ($update == 1 ? ($nomecidade == '' ? 'Uma cidade alterada' : $nomecidade) : $update . ' cidades alteradas')) ;
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }
}
