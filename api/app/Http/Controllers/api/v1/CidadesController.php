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

use App\Models\Cidades;

class CidadesController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $showall = isset($request->showall) ? boolval($request->showall) : false;
        $codigoibge = isset($request->codigoibge) ? utf8_decode($request->codigoibge) : null;
        $cidade = isset($request->cidade) ? utf8_decode($request->cidade) : null;
        if ($cidade == '') $cidade = null;
        $uf = isset($request->uf) ? utf8_decode($request->uf) : null;
        if ($uf == '') $uf = null;

        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }

        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $cidades = Cidades::with('regiao')
                    ->whereRaw('if(?=1, true, cidades.ativo=1)', [$showall])
                    ->when($find, function ($query, $find) {
                      return $query->Where('cidade', 'like', '%'.$find.'%')
                            ->orWhere('codigo_ibge', 'like', '%'.$find.'%');
                    })
                    ->when($codigoibge, function ($query, $codigoibge) {
                        return $query->Where('codigo_ibge', '=', $codigoibge);
                    })
                    ->when($request->ids, function ($query) use ($ids) {
                        return $query->whereIn('id', $ids);
                    })
                    ->when($cidade, function ($query, $cidade) {
                        return $query->Where('cidade', '=', $cidade);
                    })
                    ->when($uf, function ($query, $uf) {
                        return $query->Where('uf', '=', $uf);
                    })
                    ->orderBy('cidade')
                    ->orderby('uf')
                    ->paginate($perpage);
        $dados = [];
        foreach ($cidades as $cidade) {
            $dados[] = $cidade->toObject(true);//showCompact
        }
        $ret->data = $dados;
        $ret->collection = $cidades;
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

        $cidade = Cidades::find($find);
        if (!$cidade) throw new Exception("Cidade não foi encontrada");

        $ret->data = $cidade->toObject(false);
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
            'codigo_ibge' => [
                    'integer',
                    'required',
                    Rule::unique('cidades')->ignore(isset($request->id) ? intVal($request->id) : 0),
                ],
            'ativo' => ['required'],
            'cidade' => ['required', 'max:255'],
            'uf' => ['required', 'size:2']
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


        $estadosBrasileiros = [ 'AC'=>'Acre',
                                'AL'=>'Alagoas',
                                'AP'=>'Amapá',
                                'AM'=>'Amazonas',
                                'BA'=>'Bahia',
                                'CE'=>'Ceará',
                                'DF'=>'Distrito Federal',
                                'ES'=>'Espírito Santo',
                                'GO'=>'Goiás',
                                'MA'=>'Maranhão',
                                'MT'=>'Mato Grosso',
                                'MS'=>'Mato Grosso do Sul',
                                'MG'=>'Minas Gerais',
                                'PA'=>'Pará',
                                'PB'=>'Paraíba',
                                'PR'=>'Paraná',
                                'PE'=>'Pernambuco',
                                'PI'=>'Piauí',
                                'RJ'=>'Rio de Janeiro',
                                'RN'=>'Rio Grande do Norte',
                                'RS'=>'Rio Grande do Sul',
                                'RO'=>'Rondônia',
                                'RR'=>'Roraima',
                                'SC'=>'Santa Catarina',
                                'SP'=>'São Paulo',
                                'SE'=>'Sergipe',
                                'TO'=>'Tocantins'
        ];

        if (!array_key_exists($request->uf, $estadosBrasileiros)) {
            throw new Exception("UF inválida!");
        }


        if ($action=='update') {
            $cidade = Cidades::find($id);
            if (!$cidade) throw new Exception("Cidade não foi encontrada");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        if ($action=='add') {
            $cidade = new Cidades();
            $cidade->created_usuarioid = $usuario->id;
        }
        $cidade->ativo = $request->ativo;
        $cidade->uf = $request->uf;
        $cidade->estado = $estadosBrasileiros[$request->uf];
        $cidade->cidade = $request->cidade;
        $cidade->regiaoid = (isset($request->regiaoid) ? ($request->regiaoid > 0 ? $request->regiaoid : null) : null);
        $cidade->codigo_ibge = $request->codigo_ibge;
        $cidade->updated_usuarioid = $usuario->id;
        $cidade->save();

        DB::commit();

        $ret->id = $cidade->id;
        $ret->data = $cidade->toObject(false);
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

        $cidade = Cidades::find($find);
        if (!$cidade) throw new Exception("Cidade não foi encontrada");

        if ($cidade->regioes) {
            $regiaocount = $cidade->regioes->count();
            if ($regiaocount>0)
                throw new Exception($regiaocount==1 ? 'Existe uma região associada' :  "Existem " . $regiaocount . " regiões associadas");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }


      try {
        DB::beginTransaction();

        $del = $cidade->delete();

        DB::commit();

        $ret->msg = 'Cidade ' . $cidade->cidade . ' foi excluída!';
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }

      return $ret->toJson();
    }


}
