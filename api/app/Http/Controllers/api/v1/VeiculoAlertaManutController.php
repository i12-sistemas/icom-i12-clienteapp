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

use App\Models\Veiculo;
use App\Models\VeiculoAlertaManut;

class VeiculoAlertaManutController extends Controller
{

    public function find(Request $request, $id)
    {
      $ret = new RetApiController;
      try {
        $find = isset($id) ? intVal($id) : 0;
        if (!($find>0)) throw new Exception("Nenhum id informado");

        $manut = VeiculoAlertaManut::find($find);
        if (!$manut) throw new Exception("Manutenção não foi encontrada");

        $ret->data = $manut->toObject();
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }


    public function ligar(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'veiculoid' => ['integer', 'required'],
            'prioridade' => ['string', 'size:1', 'required'],
            'tempoprevisto' => ['integer', 'min:1', 'required']
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

        $veiculoid = isset($request->veiculoid) ? intVal($request->veiculoid) : 0;
        $veiculo = Veiculo::find($veiculoid);
        if (!$veiculo) throw new Exception("Veículo não foi encontrado");
        if (!$veiculo->ativo) throw new Exception("Veículo não está ativo");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        //revoked all before
        VeiculoAlertaManut::where('veiculoid', '=', $veiculo->id)
                            ->where('revoked', '=', 0)
                            ->update([
                                'revoked' => 1,
                                'revoked_at' => Carbon::now(),
                                'revoked_usuarioid' => $usuario->id
                            ]);

        $manut = new VeiculoAlertaManut();
        $manut->created_at = Carbon::now();
        $manut->created_usuarioid = $usuario->id;
        $manut->veiculoid = $veiculo->id;
        $manut->prioridade = $request->prioridade;
        $manut->obs = $request->obs;
        $manut->tempoprevisto = $request->tempoprevisto;
        $manut->save();

        $veiculo->alertamanutid = $manut->id;
        $veiculo->save();

        DB::commit();

        $ret->id = $manut->id;
        $ret->data = $manut->toObject(false);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function desligar(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'veiculoid' => ['integer', 'required'],
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

        $veiculoid = isset($request->veiculoid) ? intVal($request->veiculoid) : 0;
        $veiculo = Veiculo::find($veiculoid);
        if (!$veiculo) throw new Exception("Veículo não foi encontrado");


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        //revoked all before
        VeiculoAlertaManut::where('veiculoid', '=', $veiculo->id)
                            ->where('revoked', '=', 0)
                            ->update([
                                'revoked' => 1,
                                'revoked_at' => Carbon::now(),
                                'revoked_usuarioid' => $usuario->id
                            ]);

        $veiculo->alertamanutid = null;
        $veiculo->save();

        DB::commit();

        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }
}
