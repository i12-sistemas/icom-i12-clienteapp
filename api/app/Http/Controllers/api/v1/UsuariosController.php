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

use App\Models\Usuario;
use App\Models\Coletas;
use App\Models\UsuarioUnidade;

use App\Models\UsuarioResetPwdTokens;
use App\Mail\Mail\Usuario\ResetPwdRequest;

use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class UsuariosController extends Controller
{
    // public function testeexport(Request $request) {
    //     $format = mb_strtolower(isset($request->format) ? $request->format : 'xlsx');
    //     $filename = 'usuarios';
    //     if ($format == 'csv') {
    //         return Excel::download(new UsersExport, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
    //     } else if ($format == 'xls') {
    //         return Excel::download(new UsersExport, $filename . '.xls', \Maatwebsite\Excel\Excel::XLS);
    //     } else if ($format == 'pdf') {
    //         return Excel::download(new UsersExport, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
    //     } else {
    //         return Excel::download(new UsersExport, $filename . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);

    //     }
    // }

    // public function teste(Request $request) {
    //     $token = \App\Models\UsuarioResetPwdTokens::find(14);
    //     // $job = new \App\Jobs\Usuario\ResetPwdRequestJob($token);
    //     $job = new \App\Jobs\Usuario\ResetPwdChangedJob($token);
    //     $job->handle();
    // }

    // public function testeview() {
    //     $token = UsuarioResetPwdTokens::find(11);
    //     return new ResetPwdRequest($token);
    // }

    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $sortby = isset($request->sortby) ? $request->sortby : 'nome';
        $descending = isset($request->descending) ? $request->descending : 'asc';

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $ativo = null;
        if (isset($request->ativo)) {
            $ativo = explode(",", $request->ativo);
            if (!is_array($ativo)) $ativo[] = $ativo;
            $ativo = count($ativo) > 0 ? $ativo : null;
        }

        $ids = null;
        if (isset($request->ids)) {
          $ids = explode(",", $request->ids);
          if (!is_array($ids)) $ids[] = $ids;
          $ids = count($ids) > 0 ? $ids : null;
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $usuarios = Usuario::with(['unidadeprincipal'])
                    ->when($find, function ($query) use ($find) {
                      return $query->where(function($query2) use ($find) {
                        return $query2->where('usuario.nome', 'like', $find.'%')
                                ->orWhere('usuario.login', 'like', $find.'%')
                                ->orWhere('usuario.email', 'like', $find.'%')
                                ;
                      });
                    })
                    ->when(isset($request->ativo) && ($ativo != null), function ($query, $t) use ($ativo) {
                        return $query->WhereIn('usuario.ativo', $ativo);
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->orderBy($sortby, ($descending == 'desc' ? 'desc' : 'asc'))
                    ->orderby('usuario.nome', 'asc')
                    ->paginate($perpage);
        $dados = [];
        foreach ($usuarios as $usuario) {
            $dados[] = $usuario->toObject(true);
        }
        $ret->data = $dados;
        $ret->sortby = $sortby;
        $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
        $ret->collection = $usuarios;
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

        $usuario = Usuario::find($find);
        if (!$usuario) throw new Exception("Usuário não foi encontrado");

        $ret->data = $usuario->toObject(true);
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
            'login' => [
                    'string',
                    'min:3', 'max:255',
                    'required',
                    Rule::unique('usuario')->ignore(isset($request->id) ? intVal($request->id) : 0),
                ],
            'nome' => ['required', 'min:3', 'max:60'],
            'email' => ['max:255'],
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
            $usuario = Usuario::find($id);
            if (!$usuario) throw new Exception("Usuário não foi encontrado");
        }

        $email = isset($request->email) ? $request->email : '';
        if (!$email) $email = '';
        if ($email != '') {
            if (!(validEmail($email))) throw new Exception("E-mail inválido");
            $idcheck = ($action=='update') ? intVal($id) : -1;
            $usermail = Usuario::where('email', $email)->whereRaw('if(?>0, not(id=?), true)', [$idcheck, $idcheck])->first();
            if ($usermail) throw new Exception("O e-mail informado já está associado a outro usuário");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $usuario = new Usuario();
            $usuario->created_usuarioid = $usuariosession->id;
        }

        $usuario->nome = $request->nome;
        $usuario->login = $request->login;
        $usuario->ativo = $request->ativo;
        if (isset($request->defaulturl)) $usuario->defaulturl = $request->defaulturl;
        $usuario->email = (isset($request->email)) ? $request->email : null;
        if (isset($request->fotourl)) $usuario->fotourl = ($request->fotourl!=='') ? $request->fotourl : null;
        $usuario->updated_usuarioid = $usuariosession->id;
        if ($request->has('unidadeprincipalid')) $usuario->unidadeprincipalid = $request->unidadeprincipalid;
        $usuario->save();

        //insere e remomve permissões
        if (isset($request->unidadesdelete)) {
            UsuarioUnidade::where('usuarioid', $usuario->id)->whereIn('unidadeid', $request->unidadesdelete)->delete();
        }
        if (isset($request->unidadesinsert)) {
            $itensIns = [];
            foreach ($request->unidadesinsert as $unidadeid) {
                $itensIns[] = [
                    'usuarioid' => $usuario->id,
                    'created_usuarioid' => $usuariosession->id,
                    'unidadeid' => $unidadeid,
                    'created_at' => Carbon::now()
                ];
            }
            if (count($itensIns) > 0 ) {
                UsuarioUnidade::insertOrIgnore($itensIns);
            }
        }
        if ($usuario->unidadeprincipalid > 0) {
            $itensIns = [
                'usuarioid' => $usuario->id,
                'created_usuarioid' => $usuariosession->id,
                'unidadeid' => $usuario->unidadeprincipalid,
                'created_at' => Carbon::now()
            ];
            UsuarioUnidade::insertOrIgnore($itensIns);
        }

        DB::commit();

        $ret->id = $usuario->id;
        $ret->data = $usuario->toObject(true);
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

        $usuario = Usuario::find($id);
        if (!$usuario) throw new Exception("Usuário não foi encontrado");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $usuario->delete();

        DB::commit();

        $ret->msg = 'Usuário ' . $usuario->nome . ' foi excluído!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }
}
