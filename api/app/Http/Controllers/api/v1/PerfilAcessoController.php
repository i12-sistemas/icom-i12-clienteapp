<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

use App\Http\Controllers\RetApiController;

use App\Models\PerfilAcesso;
use App\Models\UsuarioPerfil;
use App\Models\PerfilacessoPermissoes;

class PerfilAcessoController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'descricao';
        $descending = isset($request->descending) ? $request->descending : 'asc';

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $ativo = null;
        if (isset($request->ativo)) {
            $ativo = explode(",", $request->ativo);
            if (!is_array($ativo)) $ativo[] = $ativo;
            $ativo = count($ativo) > 0 ? $ativo : null;
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $dataset = PerfilAcesso::with('perfis', 'permissoes')
                    ->when($find, function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        return $query2->where('perfilacesso.descricao', 'like', $find.'%');
                      });
                    })
                    ->when(isset($request->ativo) && ($ativo != null), function ($query, $t) use ($ativo) {
                        return $query->WhereIn('perfilacesso.ativo', $ativo);
                    })
                    ->orderBy($sortby, ($descending == 'desc' ? 'desc' : 'asc'))
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->toObject();
        }
        $ret->data = $dados;
        $ret->sortby = $sortby;
        $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
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

        $dataset = PerfilAcesso::find($find);
        if (!$dataset) throw new Exception("Perfil de acesso não foi encontrado");

        $ret->data = $dataset->toObject();
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function listUsuarios(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $dataset = PerfilAcesso::find($find);
        if (!$dataset) throw new Exception("Perfil de acesso não foi encontrado");

        $dados =[];
        foreach ($dataset->perfis as $perfilusuario) {
            $d = $perfilusuario->usuario->toObject();
            $d['pivot_created_usuario'] = $perfilusuario->created_usuario->toObject();
            $d['pivot_created_at'] = $perfilusuario->created_at;
            $dados[] = $d;
        }
        $ret->data = $dados;
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

        $usuariosession = session('usuario');
        if (!$usuariosession) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'descricao' => [
                    'string',
                    'min:3', 'max:255',
                    'required',
                    Rule::unique('perfilacesso')->ignore(isset($request->id) ? intVal($request->id) : 0),
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
            $dataset = PerfilAcesso::find($id);
            if (!$dataset) throw new Exception("Perfil não foi encontrado");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
            $dataset = new PerfilAcesso();
            $dataset->created_usuarioid = $usuariosession->id;
        }
        $dataset->descricao = $request->descricao;
        $dataset->ativo = $request->ativo;
        $dataset->updated_usuarioid = $usuariosession->id;
        $dataset->save();

        //insere e remomve permissões
        if (isset($request->permissoesdelete)) {
          PerfilacessoPermissoes::where('perfilid', $dataset->id)->whereIn('permissaoid', $request->permissoesdelete)->delete();
        }
        if (isset($request->permissoesinsert)) {
            $itensIns = [];
            foreach ($request->permissoesinsert as $idpermissao) {
                $itensIns[] = [
                    'perfilid' => $dataset->id,
                    'permissaoid' => $idpermissao
                ];
            }
            if (count($itensIns) > 0 ) {
              PerfilacessoPermissoes::insertOrIgnore($itensIns);
            }
        }

        //insere e remomve usuariops
        if (isset($request->usuariosdelete)) {
            UsuarioPerfil::where('perfilid', $dataset->id)->whereIn('usuarioid', $request->usuariosdelete)->delete();
        }
        if (isset($request->usuariosinsert)) {
            $itensIns = [];
            foreach ($request->usuariosinsert as $idusuario) {
                $itensIns[] = [
                    'perfilid' => $dataset->id,
                    'usuarioid' => $idusuario,
                    'created_usuarioid' => $usuariosession->id,
                    'created_at' => Carbon::now()
                ];
            }
            if (count($itensIns) > 0 ) {
                UsuarioPerfil::insertOrIgnore($itensIns);
            }
        }

        DB::commit();

        $dataset = PerfilAcesso::find($dataset->id);
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

    public function delete(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $dataset = PerfilAcesso::find($id);
        if (!$dataset) throw new Exception("Perfil não foi encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $dataset->delete();

        DB::commit();

        $ret->msg = 'Perfil de acesso ' . $dataset->descricao . ' foi excluído!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }
}
