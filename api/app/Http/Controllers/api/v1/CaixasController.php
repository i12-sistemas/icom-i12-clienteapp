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

use App\Models\Caixa;
use App\Models\CaixaDepto;

class CaixasController extends Controller
{
    public function extrato(Request $request, $deptoid)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');


        if (!($deptoid>0)) throw new Exception("Nenhum departamento informado");

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }

        $createdati = isset($request->createdati) ? $request->createdati : null;
        $createdatf = isset($request->createdatf) ? $request->createdatf : null;

        $categoria = null;
        if (isset($request->categoria)) {
            $categoria = explode(",", $request->categoria);
            if (!is_array($categoria)) $categoria[] = $categoria;
            $categoria = count($categoria) > 0 ? $categoria : null;
        }


        $deptos = CaixaDepto::select(DB::raw('caixa_depto.*'))
                ->join('caixa_depto_usuario', function ($join) use ($usuario) {
                    $join->on('caixa_depto.id', '=', 'caixa_depto_usuario.caixadeptoid')
                        ->where('caixa_depto_usuario.usuarioid', '=', $usuario->id);
                })
                ->where('caixa_depto.id', '=', $deptoid)
                ->get();
        if (!$deptos) throw new Exception("Nenhum departamento encontrado ou permitido");
        if (count($deptos) <= 0) throw new Exception("Nenhum departamento encontrado ou permitido");


        $dataset = Caixa::select(DB::raw('caixa.*'))
                    ->join('caixa_depto', 'caixa.deptoid', '=', 'caixa_depto.id')
                        // ->innerJoin('caixa_depto', 'caixa.deptoid', '=', 'caixa_depto.id')
                    // ->with('categoria', 'depto', '')
                    ->where('caixa.deptoid', '=', $deptoid)
                    ->when($find, function ($query, $find) {
                      return $query->Where('caixa.historico', 'like', $find.'%');
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('caixa.id', $ids);
                    })
                    ->when($request->createdati, function ($query) use ($createdati) {
                        return $query->Where(DB::Raw('date(caixa.created_at)'), '>=', $createdati);
                    })
                    ->when($request->createdatf, function ($query) use ($createdatf) {
                        return $query->Where(DB::Raw('date(caixa.created_at)'), '<=', $createdatf);
                    })
                    ->when(isset($request->categoria) && ($categoria != null), function ($query, $t) use ($categoria) {
                        return $query->WhereIn('caixa.categoriaid', $categoria);
                    })
                    ->orderBy('caixa.created_at', 'desc')
                    ->orderBy('caixa.id', 'desc')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export(false);
        }
        $ret->data = $dados;
        $ret->collection = $dataset;
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function resumo(Request $request)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }
        $deptos = CaixaDepto::select(DB::raw('caixa_depto.*'))
                ->join('caixa_depto_usuario', function ($join) use ($usuario) {
                    $join->on('caixa_depto.id', '=', 'caixa_depto_usuario.caixadeptoid')
                        ->where('caixa_depto_usuario.usuarioid', '=', $usuario->id);
                })
                ->when($find, function ($query, $find) {
                    return $query->Where('caixa_depto.depto', 'like', $find.'%');
                })
                ->orderBy('caixa_depto.ativo', 'desc')->get();

        $dados = [];
        foreach ($deptos as $depto) {
            $deptoRow = $depto->export(false);
            $ultimomov = Caixa::where('deptoid', '=', $depto->id)
                    ->orderBy('caixa.created_at', 'desc')
                    ->orderBy('caixa.id', 'desc')
                    ->first();
            $deptoRow['ultimomov'] = $ultimomov ? $ultimomov->export(false) : null;
            if (($depto->ativo == 1) || ($ultimomov ? $ultimomov->saldo != 0 : false)) {
                $dados[] = $deptoRow;
            }
        }
        $ret->data = $dados;
        $ret->collection = $deptos;
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

        $row = Caixa::find($find);
        if (!$row) throw new Exception("Nenhuma registro de caixa foi encontrado");

        $ret->data = $row->export(True);
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
            'historico' => ['string', 'min:1', 'max:150', 'required' ],
            'deptoid' => ['integer', 'required'],
            'categoriaid' => ['integer', 'required'],
            'valor' => ['required', 'min:0'],
            'tipo' => ['string', 'required']
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


        $deptos = CaixaDepto::select(DB::raw('caixa_depto.*'))
                ->join('caixa_depto_usuario', function ($join) use ($usuario) {
                    $join->on('caixa_depto.id', '=', 'caixa_depto_usuario.caixadeptoid')
                        ->where('caixa_depto_usuario.usuarioid', '=', $usuario->id);
                })
                ->where('caixa_depto.id', '=', $request->deptoid)
                ->get();
        if (!$deptos) throw new Exception("Nenhum departamento encontrado ou permitido");
        if (count($deptos) <= 0) throw new Exception("Nenhum departamento encontrado ou permitido");


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $saldoatual = 0;
        $ultimomov = Caixa::where('deptoid', '=', $request->deptoid)->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();
        if ($ultimomov) $saldoatual = $ultimomov->saldo;

        if ($request->tipo == 'S') {
            $saldoatual = $saldoatual - $request->valor;
        } else {
            $saldoatual = $saldoatual + $request->valor;
        }


        $caixa = new Caixa();
        $caixa->created_usuarioid = $usuario->id;
        $caixa->deptoid = $request->deptoid;
        $caixa->categoriaid = $request->categoriaid;
        $caixa->tipo = $request->tipo;
        $caixa->valor = $request->valor;
        $caixa->saldo = $saldoatual;
        $caixa->historico = $request->historico;
        $caixa->origem = 1;
        $caixa->created_at = Carbon::now();
        $caixa->save();

        DB::commit();

        $ret->id = $caixa->id;
        $ret->data = $caixa->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }
}
