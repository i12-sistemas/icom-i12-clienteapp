<?php

namespace App\Http\Controllers\API\V1;

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

use App\Models\NotaConferencia;
use App\Models\ColetasNota;
use App\Models\Cliente;

use App\Exports\NotaConferenciaExport;
use Maatwebsite\Excel\Facades\Excel;

use App\Jobs\NotaConferenciaDownloadIndex;

class NotaConferenciaController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $perpage = isset($request->perpage) ? $request->perpage : 25;
        $created_ati = isset($request->created_ati) ? $request->created_ati : null;
        $created_atf = isset($request->created_atf) ? $request->created_atf : null;
        $updated_ati = isset($request->updated_ati) ? $request->updated_ati : null;
        $updated_atf = isset($request->updated_atf) ? $request->updated_atf : null;
        $baixado = isset($request->baixado) ? boolval($request->baixado) : null;
        $notacnpj = isset($request->notacnpj) ? cleanDocMask($request->notacnpj) : null;
        $notanumero = isset($request->notanumero) ? $request->notanumero : null;
        $clientestr = isset($request->clientestr) ? $request->clientestr : null;
        $created_usuario = isset($request->created_usuario) ? $request->created_usuario : null;

        $pesoi = isset($request->pesoi) ? $request->pesoi : null;
        $pesof = isset($request->pesof) ? $request->pesof : null;
        $qtdei = isset($request->qtdei) ? $request->qtdei : null;
        $qtdef = isset($request->qtdef) ? $request->qtdef : null;

        $ids = null;
        if (isset($request->ids)) {
            $ids = explode(",", $request->ids);
            if (!is_array($ids)) $ids[] = $ids;
            $ids = count($ids) > 0 ? $ids : null;
        }


        $cliente = null;
        if (isset($request->cliente)) {
            $cliente = explode(",", $request->cliente);
            if (!is_array($cliente)) $cliente[] = $cliente;
            $cliente = count($cliente) > 0 ? $cliente : null;
        }

        $orderby = null;
        $descending = true;
        $sortby = null;
        if (isset($request->orderby)) {
            $orderby = json_decode($request->orderby,true);
            $orderbynew = [];
            foreach ($orderby as $key => $value) {
                if ($key == 'cliente') {
                    $lKey = 'trim(cliente.razaosocial)';
                } else if ($key == 'created_usuario') {
                    $lKey = 'trim(created_usuario.nome)';
                } else if ($key == 'coletaid') {
                    $lKey = 'coletas_nota.idcoleta';
                } else if ($key == 'updated_at') {
                    $a = strtoupper($value);
                    if ($a === 'ASC') {
                        $lKey = 'if(nota_conferencia.baixado=1, 0, 1)';
                    } else {
                        $lKey = 'if(nota_conferencia.baixado=1, 1, 0)';
                    }
                } else {
                    $lKey = 'nota_conferencia.' . $key;

                }
                $orderbynew[$lKey] = strtoupper($value);
                $descending = strtoupper($value) === 'DESC';
                $sortby = $key;
                if ($key == 'cliente') {
                    $orderbynew['nota_conferencia.created_at'] = strtoupper($value);
                }

                if ($key == 'updated_at') {
                    $orderbynew['if(nota_conferencia.baixado=1, nota_conferencia.updated_at, null)'] = strtoupper($value);
                }
            }
            if (count($orderbynew) > 0) {
                $orderby = $orderbynew;
            } else {
                $orderby = null;
            }
        }

        $dataset = NotaConferencia::select(DB::raw('nota_conferencia.*'))
                    ->leftJoin('cliente', 'nota_conferencia.clienteid', '=', 'cliente.id')
                    ->leftJoin('usuario as created_usuario', 'nota_conferencia.created_usuarioid', '=', 'created_usuario.id')
                    ->with('cliente', 'created_usuario', 'updated_usuario', 'coletanota', 'baixado_usuario')
                    ->when($find, function ($query, $find) {
                        $n = intVal($find);
                        $ndoc = cleanDocMask($find);
                        return $query->Where('nota_conferencia.notachave', 'like', '%'.$find.'%')
                            ->orWhere('nota_conferencia.notacnpj', 'like','%'. formatCnpjCpf($ndoc).'%')

                            ->orWhere('cliente.razaosocial', 'like', '%'.$find.'%')
                            ->orWhere('cliente.fantasia', 'like', '%'.$find.'%')
                            ->orWhere('cliente.fantasia_followup', 'like','%'. $find.'%')
                            ->orWhere('cliente.cnpj', 'like',  '%'. formatCnpjCpf($ndoc).'%')

                            ->orWhereRaw('if(?>0, nota_conferencia.notanumero=?, false)', [$n, $n]);
                    })
                    ->when(isset($request->clientestr) && ($clientestr ? $clientestr !== '' : false), function ($query) use ($clientestr)  {
                        return $query->where(function($query2) use ($clientestr) {
                            return $query2->where('cliente.razaosocial', 'like', '%'.$clientestr.'%')
                            ->orWhere('cliente.fantasia', 'like', '%'.$clientestr.'%');
                        });
                    })
                    ->when(isset($request->created_usuario) && ($created_usuario ? $created_usuario !== '' : false), function ($query) use ($created_usuario)  {
                        return $query->where(function($query2) use ($created_usuario) {
                            return $query2->where('created_usuario.nome', 'like', '%'.$created_usuario.'%')
                            ->orWhere('created_usuario.login', 'like', '%'.$created_usuario.'%');
                        });
                    })
                    ->when(isset($request->notacnpj), function ($query) use ($notacnpj) {
                        return $query->Where('nota_conferencia.notacnpj', 'like', '%'.formatCnpjCpf($notacnpj).'%');
                    })
                    ->when(isset($request->id), function ($query) use ($request) {
                        return $query->Where('nota_conferencia.id', '=', intval($request->id));
                    })
                    ->when(isset($request->coletaid), function ($query) use ($request) {
                        $chaves = null;
                        $coletas = ColetasNota::select('notachave')->where('idcoleta', '=', $request->coletaid)->get();
                        if ($coletas) {
                            $chaves = $coletas->pluck('notachave');
                        }
                        return $query->WhereIn('nota_conferencia.notachave', $chaves);
                    })
                    ->when(isset($request->notachave), function ($query) use ($request) {
                        return $query->Where('nota_conferencia.notachave', 'like', '%'. $request->notachave .'%');
                    })
                    ->when(isset($request->notanumero), function ($query) use ($notanumero) {
                        return $query->Where('nota_conferencia.notanumero', '=', $notanumero);
                    })
                    ->when(isset($request->created_ati), function ($query) use ($created_ati) {
                        return $query->Where(DB::Raw('date(nota_conferencia.created_at)'), '>=', $created_ati);
                    })
                    ->when(isset($request->created_atf), function ($query) use ($created_atf) {
                        return $query->Where(DB::Raw('date(nota_conferencia.created_at)'), '<=', $created_atf);
                    })
                    ->when(isset($request->baixado), function ($query) use ($baixado) {
                        return $query->Where('nota_conferencia.baixado', '=', $baixado);
                    })
                    ->when(isset($request->updated_ati), function ($query) use ($updated_ati) {
                        return $query->Where(DB::Raw('date(nota_conferencia.baixado_at)'), '>=', $updated_ati)
                                ->where('nota_conferencia.baixado', '=', 1);
                    })
                    ->when(isset($request->updated_atf), function ($query) use ($updated_atf) {
                        return $query->Where(DB::Raw('date(nota_conferencia.baixado_at)'), '<=', $updated_atf)
                                ->where('nota_conferencia.baixado', '=', 1);
                    })
                    ->when(isset($request->ids), function ($query) use ($ids) {
                        return $query->whereIn('nota_conferencia.id', $ids);
                    })
                    ->when(isset($request->cliente) && ($cliente != null), function ($query, $t) use ($cliente) {
                        return $query->WhereIn('nota_conferencia.clienteid', $cliente);
                    })
                    ->when(isset($request->pesoi), function ($query) use ($pesoi) {
                        return $query->Where('nota_conferencia.peso', '>=', $pesoi);
                    })
                    ->when(isset($request->pesof), function ($query) use ($pesof) {
                        return $query->Where('nota_conferencia.peso', '<=', $pesof);
                    })
                    ->when(isset($request->qtdei), function ($query) use ($qtdei) {
                        return $query->Where('nota_conferencia.qtde', '>=', $qtdei);
                    })
                    ->when(isset($request->qtdef), function ($query) use ($qtdef) {
                        return $query->Where('nota_conferencia.qtde', '<=', $qtdef);
                    })
                    ->when($request->orderby, function ($query) use ($orderby) {
                        foreach ($orderby as $key => $value) {
                            $query->orderByRaw($key  . ' ' . $value);
                        }
                        return $query;
                    })
                    ->paginate($perpage);

        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export(false);
        }

        $ret->data = $dados;
        $ret->collection = $dataset;
        $ret->sortby = $sortby;
        $ret->descending = ($descending == 'desc' ? 'desc' : 'asc');
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();

    }


    public function saveadd(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'notachave' => ['string', 'size:44', 'required' ],
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

        $notachave = isset($request->notachave) ? $request->notachave : '';
        $chaveItens = decodeChaveNFe($notachave);

        $row = NotaConferencia::where('notachave', '=', $notachave)->first();
        if ($row) throw new Exception("Esta chave já foi cadastrada em " . $row->created_at->format('d/m/Y'));

        $cliente = Cliente::where('cnpj', '=', $chaveItens['CNPJ'])->first();
        if (!$cliente) throw new Exception("Nenhum cliente identificado o CNPJ " . $chaveItens['CNPJ']);

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        $dataset = new NotaConferencia();
        $dataset->created_usuarioid = $usuario->id;
        $dataset->updated_usuarioid = $usuario->id;
        $dataset->clienteid = $cliente->id;
        $dataset->notacnpj = $chaveItens['CNPJ'];
        $dataset->notanumero = intval($chaveItens['nNF']);
        $dataset->notachave = $notachave;
        $dataset->baixado = 0;
        $dataset->peso = 0;
        $dataset->qtde = 0;
        $dataset->baixanfestatus = 0;
        $dataset->xmlprocessado = 0;
        $dataset->baixanfetentativas = 0;
        $dataset->save();

        DB::commit();

        $this->dispatch(new NotaConferenciaDownloadIndex($dataset));

        $ret->id = $dataset->id;
        $ret->data = $dataset->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function savebaixa(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'notachave' => ['string', 'size:44', 'required' ],
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

        $notachave = isset($request->notachave) ? $request->notachave : '';

        $row = NotaConferencia::where('notachave', '=', $notachave)->first();
        if (!$row) throw new Exception("Nenhum chave encontrada com o número " . $notachave);
        if ($row->baixado === 1) throw new Exception("Esta chave já foi baixada em " . $row->updated_at->format('d/m/Y  -  H:i'));

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();


        $row->updated_usuarioid = $usuario->id;
        $row->baixado_usuarioid = $usuario->id;
        $row->baixado_at = Carbon::now();
        $row->baixado = 1;
        $row->save();

        DB::commit();

        $ret->id = $row->id;
        $ret->data = $row->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function saveeditmanual(Request $request)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $rules = [
            'notachave' => ['string', 'size:44', 'required' ],
            'peso' => ['numeric', 'min:0', 'required' ],
            'qtde' => ['numeric', 'min:0', 'required' ]
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

        $notachave = isset($request->notachave) ? $request->notachave : '';

        $row = NotaConferencia::where('notachave', '=', $notachave)->first();
        if (!$row) throw new Exception("Nenhum chave encontrada com o número " . $notachave);
        // if ($row->baixado === 1) throw new Exception("Esta chave já foi baixada em " . $row->updated_at->format('d/m/Y  -  H:i'));

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $row->peso = $request->peso;
        $row->qtde = $request->qtde;
        $row->editadomanual = 1;
        $row->updated_usuarioid = $usuario->id;
        $row->updated_at = Carbon::now();
        $row->save();

        DB::commit();

        $ret->id = $row->id;
        $ret->data = $row->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function print_listagem (Request $request)
    {
        $ret = new RetApiController;
        try {
            ini_set('memory_limit', '-1');
            $disk = Storage::disk('public');
            $usuario = session('usuario');
            $output = mb_strtolower(isset($request->output) ? $request->output : 'pdf');

            $find = isset($request->find) ? utf8_decode($request->find) : null;
            $created_ati = isset($request->created_ati) ? $request->created_ati : null;
            $created_atf = isset($request->created_atf) ? $request->created_atf : null;
            $updated_ati = isset($request->updated_ati) ? $request->updated_ati : null;
            $updated_atf = isset($request->updated_atf) ? $request->updated_atf : null;
            $baixado = isset($request->baixado) ? boolval($request->baixado) : null;
            $notacnpj = isset($request->notacnpj) ? cleanDocMask($request->notacnpj) : null;
            $notanumero = isset($request->notanumero) ? $request->notanumero : null;
            $clientestr = isset($request->clientestr) ? $request->clientestr : null;
            $created_usuario = isset($request->created_usuario) ? $request->created_usuario : null;
            $coletaid = isset($request->coletaid) ? $request->coletaid : null;
            $id = isset($request->id) ? $request->id : null;

            $pesoi = isset($request->pesoi) ? $request->pesoi : null;
            $pesof = isset($request->pesof) ? $request->pesof : null;
            $qtdei = isset($request->qtdei) ? $request->qtdei : null;
            $qtdef = isset($request->qtdef) ? $request->qtdef : null;

            $ids = null;
            if (isset($request->ids)) {
                $ids = explode(",", $request->ids);
                if (!is_array($ids)) $ids[] = $ids;
                $ids = count($ids) > 0 ? $ids : null;
            }


            $cliente = null;
            if (isset($request->cliente)) {
                $cliente = explode(",", $request->cliente);
                if (!is_array($cliente)) $cliente[] = $cliente;
                $cliente = count($cliente) > 0 ? $cliente : null;
            }
            $orderby = null;
            $descending = true;
            $sortby = null;
            if (isset($request->orderby)) {
                $orderby = json_decode($request->orderby,true);
                $orderbynew = [];
                foreach ($orderby as $key => $value) {
                    if ($key == 'cliente') {
                        $lKey = 'trim(cliente.razaosocial)';
                    } else if ($key == 'created_usuario') {
                        $lKey = 'trim(created_usuario.nome)';
                    } else if ($key == 'coletaid') {
                        $lKey = 'coletas_nota.idcoleta';
                    } else if ($key == 'updated_at') {
                        $a = strtoupper($value);
                        if ($a === 'ASC') {
                            $lKey = 'if(nota_conferencia.baixado=1, 0, 1)';
                        } else {
                            $lKey = 'if(nota_conferencia.baixado=1, 1, 0)';
                        }
                    } else {
                        $lKey = 'nota_conferencia.' . $key;

                    }
                    $orderbynew[$lKey] = strtoupper($value);
                    $descending = strtoupper($value) === 'DESC';
                    $sortby = $key;
                    if ($key == 'cliente') {
                        $orderbynew['nota_conferencia.created_at'] = strtoupper($value);
                    }

                    if ($key == 'updated_at') {
                        $orderbynew['if(nota_conferencia.baixado=1, nota_conferencia.updated_at, null)'] = strtoupper($value);
                    }
                }
                if (count($orderbynew) > 0) {
                    $orderby = $orderbynew;
                } else {
                    $orderby = null;
                }
            }

            $temfiltro = (($id) || ($coletaid) || ($clientestr) || ($created_usuario) || ($find) || ($created_ati) || ($created_atf) || ($updated_ati) || ($updated_atf) || ($baixado) || ($notacnpj) || ($notanumero) || ($ids) || ($cliente) || ($pesoi) || ($pesof) || ($qtdei) || ($qtdef));
            if (!$temfiltro) throw new Exception('Informe um filtro para limitar sua impressão');


            $rows = NotaConferencia::select(DB::raw('nota_conferencia.*'))
                        ->leftJoin('cliente', 'nota_conferencia.clienteid', '=', 'cliente.id')
                        ->leftJoin('usuario as created_usuario', 'nota_conferencia.created_usuarioid', '=', 'created_usuario.id')
                        ->with('cliente', 'created_usuario', 'updated_usuario', 'coletanota')
                        ->when($find, function ($query, $find) {
                            $n = intVal($find);
                            $ndoc = cleanDocMask($find);
                            return $query->Where('nota_conferencia.notachave', 'like', '%'.$find.'%')
                                ->orWhere('nota_conferencia.notacnpj', 'like','%'. formatCnpjCpf($ndoc).'%')

                                ->orWhere('cliente.razaosocial', 'like', '%'.$find.'%')
                                ->orWhere('cliente.fantasia', 'like', '%'.$find.'%')
                                ->orWhere('cliente.fantasia_followup', 'like','%'. $find.'%')
                                ->orWhere('cliente.cnpj', 'like',  '%'. formatCnpjCpf($ndoc).'%')

                                ->orWhereRaw('if(?>0, nota_conferencia.notanumero=?, false)', [$n, $n]);
                        })
                        ->when(isset($request->clientestr) && ($clientestr ? $clientestr !== '' : false), function ($query) use ($clientestr)  {
                            return $query->where(function($query2) use ($clientestr) {
                                return $query2->where('cliente.razaosocial', 'like', '%'.$clientestr.'%')
                                ->orWhere('cliente.fantasia', 'like', '%'.$clientestr.'%');
                            });
                        })
                        ->when(isset($request->created_usuario) && ($created_usuario ? $created_usuario !== '' : false), function ($query) use ($created_usuario)  {
                            return $query->where(function($query2) use ($created_usuario) {
                                return $query2->where('created_usuario.nome', 'like', '%'.$created_usuario.'%')
                                ->orWhere('created_usuario.login', 'like', '%'.$created_usuario.'%');
                            });
                        })
                        ->when(isset($request->notacnpj), function ($query) use ($notacnpj) {
                            return $query->Where('nota_conferencia.notacnpj', 'like', '%'.formatCnpjCpf($notacnpj).'%');
                        })
                        ->when(isset($request->id), function ($query) use ($request) {
                            return $query->Where('nota_conferencia.id', '=', intval($request->id));
                        })
                        ->when(isset($request->coletaid), function ($query) use ($request) {
                            $chaves = null;
                            $coletas = ColetasNota::select('notachave')->where('idcoleta', '=', $request->coletaid)->get();
                            if ($coletas) {
                                $chaves = $coletas->pluck('notachave');
                            }
                            return $query->WhereIn('nota_conferencia.notachave', $chaves);
                        })
                        ->when(isset($request->notachave), function ($query) use ($request) {
                            return $query->Where('nota_conferencia.notachave', 'like', '%'. $request->notachave .'%');
                        })
                        ->when(isset($request->notanumero), function ($query) use ($notanumero) {
                            return $query->Where('nota_conferencia.notanumero', '=', $notanumero);
                        })
                        ->when(isset($request->created_ati), function ($query) use ($created_ati) {
                            return $query->Where(DB::Raw('date(nota_conferencia.created_at)'), '>=', $created_ati);
                        })
                        ->when(isset($request->created_atf), function ($query) use ($created_atf) {
                            return $query->Where(DB::Raw('date(nota_conferencia.created_at)'), '<=', $created_atf);
                        })
                        ->when(isset($request->baixado), function ($query) use ($baixado) {
                            return $query->Where('nota_conferencia.baixado', '=', $baixado);
                        })
                        ->when(isset($request->updated_ati), function ($query) use ($updated_ati) {
                            return $query->Where(DB::Raw('date(nota_conferencia.updated_at)'), '>=', $updated_ati)
                                    ->where('nota_conferencia.baixado', '=', 1);
                        })
                        ->when(isset($request->updated_atf), function ($query) use ($updated_atf) {
                            return $query->Where(DB::Raw('date(nota_conferencia.updated_at)'), '<=', $updated_atf)
                                    ->where('nota_conferencia.baixado', '=', 1);
                        })
                        ->when(isset($request->ids), function ($query) use ($ids) {
                            return $query->whereIn('nota_conferencia.id', $ids);
                        })
                        ->when(isset($request->cliente) && ($cliente != null), function ($query, $t) use ($cliente) {
                            return $query->WhereIn('nota_conferencia.clienteid', $cliente);
                        })
                        ->when(isset($request->pesoi), function ($query) use ($pesoi) {
                            return $query->Where('nota_conferencia.peso', '>=', $pesoi);
                        })
                        ->when(isset($request->pesof), function ($query) use ($pesof) {
                            return $query->Where('nota_conferencia.peso', '<=', $pesof);
                        })
                        ->when(isset($request->qtdei), function ($query) use ($qtdei) {
                            return $query->Where('nota_conferencia.qtde', '>=', $qtdei);
                        })
                        ->when(isset($request->qtdef), function ($query) use ($qtdef) {
                            return $query->Where('nota_conferencia.qtde', '<=', $qtdef);
                        })
                        ->when($request->orderby, function ($query) use ($orderby) {
                            foreach ($orderby as $key => $value) {
                                $query->orderByRaw($key  . ' ' . $value);
                            }
                            return $query;
                        })
                        ->get();

            if (!$rows) throw new Exception('Nenhum registro encontrado');
            if (count($rows) == 0) throw new Exception('Nenhum registro encontrado');
            if (count($rows) > 1500) throw new Exception('Limite de 1500 registros foi excedido. Informe os filtros para limitar a consulta.');

            if ($output == 'pdf') {


                $path = $disk->path('temp');
                if (!$disk->exists('temp')) $disk->makeDirectory('temp');

                $html = view('pdf.notaconferencia.listagem', compact('rows', 'usuario'))->render();

                // $config = [
                //     'title' => 'Listagem de entrada de notas',
                //     'author'=> ENV('APP_NAME',''),
                //     'orientation' => 'L',
                //     'format' => 'A4',
                //     'margin_left'          => 10,
                //     'margin_right'         => 10,
                //     'margin_top'           => 10,
                //     'margin_bottom'        => 10,
                //     'margin_header'        => 0,
                //     'margin_footer'        => 0,
                // ];
                // $pdf = PDF::loadHtml($html, $config);
                $pdf = PDF::loadHtml($html);
                $pdf->setPaper('A4', 'landscape');

                $filename = 'notaconferencia-listagem-' . md5($html) . '.pdf';

                $file = 'temp/' . $filename;

                if (!$disk->exists($file)) $disk->delete($file);
                $pdf->save($disk->path($file));

                if (!$disk->exists('temp/' . $filename))
                    throw new Exception('Falha ao gerar PDF. Arquivo não foi encontrado no disco.');

                if ($output == 'teste') {
                    return $disk->download('temp/' . $filename, $filename, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="'.$filename.'"'
                    ]);
                }

                $ret->ok = true;
                $ret->msg = $disk->url($file);
            } else {
                $ret->msg = self::exportFile($rows, $output);
                $ret->ok = true;
            }


            return $ret->toJson();
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }
    }

    public function exportFile($dataset, $format) {
        try {
            $format = mb_strtolower($format);
            if (!(($format=='xlsx') || ($format=='csv') || ($format=='xls')))
                throw new Exception('Formato inválido. Permitido somente XLSX, XLS, CSV');

            $path = 'export/' . Carbon::now()->format('Y-m-d') . '/';
            $filename = 'notas-entradas-' . Carbon::now()->format('Y-m-d-H-i-s-') . md5(createRandomVal(5) . Carbon::now()) . '.' . $format;
            $fullfilename = '';

            $export = new NotaConferenciaExport($dataset);
            $fullfilename =  $path . $filename;

            $formatExport = \Maatwebsite\Excel\Excel::XLSX;
            switch ($format) {
                case 'csv':
                    $formatExport = \Maatwebsite\Excel\Excel::CSV;
                    break;

                case 'xls':
                    $formatExport = \Maatwebsite\Excel\Excel::XLS;
                    break;

                default:
                    $formatExport = \Maatwebsite\Excel\Excel::XLSX;
                    break;
            }

            Excel::store($export, $fullfilename, 'public', $formatExport);

            $disk = Storage::disk('public');
            if (!$disk->exists($fullfilename)) throw new Exception('Nenhum arquivo encontrado no disco');

            return $disk->url($fullfilename);

        } catch (\Throwable $th) {
            throw new Exception('Erro ao gerar arquivo - ' . $th->getMessage());
        }
    }


    //index xml nfe para tabela
    public function processa(Request $request)
    {
        $ret = new RetApiController;
        try {
            $limite = isset($request->pagesize) ? intval($request->pagesize) : 20;
            $chaves = null;
            if (isset($request->chaves)) {
                $chaves = explode(",", $request->chaves);
                if (!is_array($chaves)) $chaves[] = $chaves;
                $chaves = count($chaves) > 0 ? $chaves : null;
            }

            $notas = NotaConferencia::where('baixanfestatus', '=', 1)
                            ->where('xmlprocessado', '=', 0)
                            ->when(isset($request->chaves), function ($query) use ($chaves) {
                                return $query->whereIn('notachave', $chaves);
                            })
                            ->orderBy('created_at', 'desc')
                            ->paginate($limite);
            if (!$notas) throw new Exception('Nenhum registro a ser processado');
            if (count($notas) == 0) throw new Exception('Nenhum registro a ser processado');

            $disk = Storage::disk('public');

            foreach ($notas as $nota) {
                $arquivoxml = $disk->get($nota->storageurl);

                // Transformando o conteúdo XML da variável $string em Objeto
                $xml = simplexml_load_string($arquivoxml);

                $nfe = $xml->NFe;
                // $nfe = $xml; //usado pra teste nota em digitação sem assinatura
                $qVol = 0;
                $pesoB = 0;
                foreach ($nfe->infNFe->transp->vol as $key => $vol) {
                    $qVol = $qVol + floatVal($vol->qVol);
                    $pesoB = $pesoB + floatVal($vol->pesoB);
                }

                try {
                    DB::beginTransaction();

                    $nota->xmlprocessado = 1;
                    $nota->peso = $pesoB;
                    $nota->qtde = $qVol;
                    $nota->save();

                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    throw new Exception('Erro ao salvar no banco de dados - ' . $th->getMessage());
                }

            }

            $notas = NotaConferencia::where('baixanfestatus', '=', 1)
                        ->where('xmlprocessado', '=', 0)
                        ->count();
            $ret->ok = true;
            $ret->data = $notas;
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();

    }

}
