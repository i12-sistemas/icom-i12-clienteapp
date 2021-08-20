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

use App\Exports\FollowupExport;
use App\Imports\FollowupPlanilhaImport;

use Maatwebsite\Excel\Facades\Excel;

use App\Http\Controllers\RetApiController;

use App\Models\FollowupFiles;
use App\Models\Followup;
use App\Models\FollowupErros;
use App\Models\Cliente;
use App\Models\Cidades;

use App\Jobs\FollowUpStartProcessaPlanilhaJob;
use App\Jobs\FollowUpExportPlanilhaJob;

class FollowupFilesController extends Controller
{
    public function list(Request $request)
    {
      $ret = new RetApiController;
      try {
        $find = isset($request->find) ? utf8_decode($request->find) : null;
        $action = isset($request->action) ? intval($request->action) : null;
        // $razaosocial = isset($request->razaosocial) ? $request->razaosocial : null;
        // $showall = isset($request->showall) ? boolval($request->showall) : false;
        // $ids = null;
        // if (isset($request->ids)) {
        //     $ids = explode(",", $request->ids);
        //     if (!is_array($ids)) $ids[] = $ids;
        //     $ids = count($ids) > 0 ? $ids : null;
        // }
        // $orderby = null;
        // $sortby = 'ASC';
        // if (isset($request->orderby)) {
        //     $orderby = json_decode($request->orderby,true);
        //     $orderbynew = [];
        //     foreach ($orderby as $key => $value) {
        //         if ($key == 'cidade') {
        //             $lKey = 'concat(cidades.cidade,cidades.uf)';
        //         } else if ($key == 'ids') {
        //             $lKey = 'cliente.id';
        //         } else {
        //             $lKey = 'cliente.' . $key;

        //         }
        //         $sortby = mb_strtoupper($value);
        //         $orderbynew[$lKey] = $sortby;
        //     }
        //     $orderbynew['cidades.cidade'] = $sortby;
        //     $orderbynew['cliente.id'] = $sortby;
        //     if (count($orderbynew) > 0) {
        //         $orderby = $orderbynew;
        //     } else {
        //         $orderby = null;
        //     }
        // }
        $perpage = isset($request->perpage) ? $request->perpage : 20;
        $dataset = FollowupFiles::select(DB::raw('followup_files.*'))
                    // ->leftJoin('cidades', 'cliente.cidadeid', '=', 'cidades.id')
                    ->with( 'created_usuario')
                    ->whereRaw('if(?>=1, followup_files.action=?, true)', [$action, $action])
                    ->when(isset($request->find) && ($find !== ''), function ($query) use ($find) {
                        return $query->where(function($query2) use ($find) {
                            $n = intval($find);
                            return $query2->Where('followup_files.md5file', 'like', '%'.$find.'%')
                            ->orWhere('followup_files.nomeoriginal', 'like', '%'.$find.'%')
                            ->orWhere('followup_files.log', 'like', '%'.$find.'%')
                            ->orWhereRaw('if(?>0, followup_files.id=?, true)', [$n, $n]);
                        });
                    })
                    ->when(isset($request->id), function ($query) use ($request)  {
                        return $query->Where('followup_files.id', '=', $request->id);
                    })
                    ->when(isset($request->nomeoriginal) ? $request->nomeoriginal !== '' : false, function ($query) use ($request)  {
                        return $query->Where('followup_files.nomeoriginal', 'like', '%'. $request->nomeoriginal . '%');
                    })
                    ->orderby('created_at', 'desc')
                    ->paginate($perpage);
        $dados = [];
        foreach ($dataset as $row) {
            $dados[] = $row->export();
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


    public function addfile (Request $request)
    {
        $ret = new RetApiController;
        try {

            $usuario = session('usuario');
            if (!$usuario) throw new Exception('Nenhum usuário autenticado');

            $rules = [
                'dataref' => ['required'],
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


            $arquivo = $request->file('arquivo');
            if (!$arquivo) throw new Exception('Nenhum arquivo enviado');

            $nomeoriginal = $arquivo->getClientOriginalName();
            $ext = $arquivo->getClientOriginalExtension();
            $size = $arquivo->getSize();
            $extallow = ['xlsx', 'xls'];
            if (!in_array($ext, $extallow)) throw new Exception('Arquivo com extensão ' . $ext . ' não é permitido. Extensões permitidas ' . implode(', ', $extallow));

            $maxsizebytes = 10485760;
            if ($size > $maxsizebytes) throw new Exception('Tamanho do arquivo ' . humanReadBytes($size) . ' excede o tamanho permitido. Tamanho máximo é de ' . humanReadBytes($maxsizebytes));

            $md5 = md5_file($arquivo->getRealPath());

            $path = 'followup/planilhas/' . Carbon::now()->format('Y-m-d') . '/';
            $file = $md5 . '.' . $ext;
            $fullnamefile = $path . $file;

            $dataset = FollowupFiles::where('md5file', '=', $md5)->first();
            if ($dataset) throw new Exception('Este arquivo foi inserido em ' . $dataset->created_at->format('d/m/Y'));

            $disk = Storage::disk();
            $checkarquivo = $disk->exists($fullnamefile);
            if ($checkarquivo) $disk->delete($fullnamefile);;

            if (!$disk->exists($path)) $disk->makeDirectory($path);

            $arquivo->storeAs($path, $file);
            if (!$disk->exists($fullnamefile)) throw new Exception('Arquivo não foi salvo');

            $dataref = Carbon::parse($request->dataref);
        } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
          return $ret->toJson();
        }

        try {
            DB::beginTransaction();

            $filefup = new FollowupFiles();

            $filefup->dataref = $dataref;
            $filefup->md5file = $md5;
            $filefup->nomeoriginal = $nomeoriginal;
            $filefup->size = $size;
            $filefup->storageurl = $fullnamefile;
            $filefup->action = 1; //1=import, 2=export
            $filefup->processado = 0;
            $filefup->storage = 'local';
            $filefup->created_usuarioid = $usuario->id;
            $filefup->created_at = Carbon::now();
            $filefup->save();

            DB::commit();

            $ret->data = $filefup->export(true);
            $ret->ok = true;
            $ret->msg = 'Arquivo inserido e incluido na fila de processamento!';

            try {
                $this->dispatch(new FollowUpStartProcessaPlanilhaJob($filefup));
            } catch (\Throwable $th) {
                $ret->msg = 'Arquivo inserido e incluido na fila de processamento, porém ocorreu um erro ao iniciar o processamento :: ' . $th->getMessage();
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg  = 'Erro ao inseir arquivo! :: ' . $th->getMessage();
        }
        return $ret->toJson();
    }

    public function exportfile (Request $request)
    {
        $ret = new RetApiController;
        try {
            $usuario = session('usuario');
            if (!$usuario) throw new Exception('Nenhum usuário autenticado');

            $rules = [
                'dti' => ['date_format:Y-m-d'],
                'dtf' => ['date_format:Y-m-d'],
                'tipoexport' => ['integer', 'required']
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

            $dti = Carbon::parse($request->dti);
            $dtf = Carbon::parse($request->dtf);


        } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
          return $ret->toJson();
        }

        try {
            DB::beginTransaction();

            $filefup = new FollowupFiles();

            $filefup->dataref = $dti;
            $filefup->dataref2 = $dtf;
            $filefup->tipoexport = $request->tipoexport;
            $filefup->md5file = md5(($dti ? $dti : '') . ($dtf ? $dtf : '') . $request->tipoexport . $usuario->id . Carbon::now());
            $filefup->nomeoriginal = 'export-temporario-' . $filefup->md5file . '.xlsx';
            $filefup->size = 0;
            $filefup->storageurl = 'followup/export/' . $filefup->nomeoriginal;
            $filefup->action = 2; //1=import, 2=export
            $filefup->processado = 0;
            $filefup->storage = 'local';
            $filefup->created_usuarioid = $usuario->id;
            $filefup->created_at = Carbon::now();
            $filefup->save();

            DB::commit();

            $this->dispatch(new FollowUpExportPlanilhaJob($filefup));

            $ret->ok = true;
            $ret->data = $filefup->export(true);
            $ret->msg = 'Solicitação de exportação incluida na fila de processamento!';
        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
    }


    public function exportprocessa ($fileid)
    {
        $ret = new RetApiController;
        try {
            ini_set('memory_limit', '-1');
            $dataset = FollowupFiles::find($fileid);
            if (!$dataset) throw new Exception('Nenhum requisição de export foi encontrada');
            if ($dataset->action !== 2) throw new Exception('Requisição não é de export');
            if ($dataset->processado === 1) throw new Exception('Requisição ja foi processada');
            if ($dataset->processado === 2) throw new Exception('Requisição em processamento');
            if ($dataset->processado !== 0) throw new Exception('Status atual não permite processamento');

        } catch (\Throwable $th) {
          $ret->msg = $th->getMessage();
          return $ret->toJson();
        }

        try {
            DB::beginTransaction();

            $dataset->processado = 2;//em processamento
            $dataset->save();

            // $this->dispatch(new FollowUpStartProcessaPlanilhaJob($filefup));
            /// isso vai pra job
            $retProcesso = self::exportexcel($dataset->id);
            if (!$retProcesso->ok)
                throw new Exception($retProcesso->msg);
            /// isso vai pra job

            DB::commit();

            $ret->ok = true;
            $ret->msg = $retProcesso->msg ? $retProcesso->msg : null;
            $ret->data = $retProcesso->data ? $retProcesso->data : null;
        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }
        return $ret->toJson();
    }

    public function exportexcel ($fileid)
    {
        $ret = new RetApiController;
        try {
            $starttime = microtime(true);
            $dhstart = Carbon::now();
            $filefup = FollowupFiles::find($fileid);
            if (!$filefup) throw new Exception('Nenhum requisição de export foi encontrada');
            if ($filefup->processado !== 2) throw new Exception('Status atual não permite exportação');

            $filterdata  = ($filefup->tipoexport === 1) || ($filefup->tipoexport === 2);

            $dataset = Followup::with('cliente', 'coleta', 'erroagenda', 'errocoleta', 'errodtpromessa', 'coleta')
                            ->when(($filterdata  &&  ($filefup->tipoexport === 1)), function ($query) use ($filefup)  {
                                return $query->whereBetween(\DB::raw('DATE(dhimportacao)'), [$filefup->dataref->format('Y-m-d'),  $filefup->dataref2->format('Y-m-d')]);
                            })
                            ->when(($filterdata  &&  ($filefup->tipoexport === 2)), function ($query) use ($filefup)  {
                                return $query->whereBetween(\DB::raw('DATE(datahora_followup)'), [$filefup->dataref->format('Y-m-d'),  $filefup->dataref2->format('Y-m-d')]);
                            })
                            ->orderBy('id', 'asc')
                            ->get();

            // if (!$dataset) throw new Exception('Nenhum registro a ser exportado');

            $path = 'followup/export/';
            $file = 'export-' . Carbon::now()->format('d-m-y') . '-id-' . $filefup->id . '-' . $filefup->md5file . '.xlsx';
            $fullnamefile = $path . $file;

            $export = new FollowupExport($dataset);
            Excel::store($export, $fullnamefile, 'public', \Maatwebsite\Excel\Excel::XLSX);

            $disk = Storage::disk('public');
            if (!$disk->exists($fullnamefile)) throw new Exception('Nenhum arquivo encontrado no disco');

            $data = [
                'public' => $disk->url($fullnamefile),
                'internal' => $fullnamefile
            ];

            try {
                DB::beginTransaction();

                $endtime = microtime(true);
                $timediff = $endtime - $starttime;

                $filefup->size = $disk->size($fullnamefile);
                $filefup->storageurl = $fullnamefile;
                $filefup->storage = 'local';
                $filefup->nomeoriginal = $file;
                $filefup->processado = 1;
                $filefup->processostart = $dhstart;
                $filefup->processoend = Carbon::now();
                $filefup->numlinhas = count($dataset);
                $filefup->numerros = 0;
                $filefup->tempoprocessamento = $timediff;
                $filefup->save();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new Exception('Erro ao salvar dados do processamento :: ' . $th->getMessage());
            }


            $ret->data = $data;
            $ret->ok = true;
        } catch (\Throwable $th) {
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

            $dataset = FollowupFiles::find($id);
            if (!$dataset) throw new Exception("Nenhum registro encontrado");
            if ($dataset->action === 1) {
                // if ($dataset->processado === 1) {
                //     if ($dataset->numlinhas > $dataset->numerros) throw new Exception("Arquivo já foi processado com linha importada");
                // } else {
                //     if ($dataset->numlinhas !== $dataset->numerros) throw new Exception("Status de processamento não permite exclusão pois existem linhas processadas");
                // }
            }


			} catch (\Throwable $th) {
				$ret->msg = $th->getMessage();
				return $ret->toJson();
			}

			try {
				$errofile = null;
				$disk = Storage::disk($dataset->action === 1 ? 'local' : 'public');
			    if ($disk->exists($dataset->storageurl)) $disk->delete($dataset->storageurl);
			} catch (\Throwable $th) {
                $errofile = $th->getMessage();
                \Log::error('Erro ao deletar arquivo :: ' . $errofile);
			}


			try {
				DB::beginTransaction();

				$del = $dataset->delete();

				DB::commit();

				$ret->msg = 'Arquivo ' . $dataset->nomeoriginal . ' foi excluído!';
				$ret->ok = true;
			} catch (\Throwable $th) {
				DB::rollBack();
				$ret->msg = $th->getMessage();
			}

			return $ret->toJson();
	}

    public function deletemass(Request $request)
    {
        $ret = new RetApiController;
        try {
            $ids = isset($request->ids) ? $request->ids : null;
            if (!$ids) throw new Exception('Nenhum dados informado');
            if (!is_array($ids)) throw new Exception('Dados informados fora do padrão');

            $dataset = FollowupFiles::whereIn('id', $ids)->get();
            if (!$dataset) throw new Exception("Nenhum registro encontrado");

            foreach ($dataset as $row) {
                if ($row->action === 1) {
                    if ($row->processado === 1) {
                        if ($row->numlinhas > $dataset->numerros) throw new Exception("Arquivo id " . $row->id . " já foi processado com linha importada");
                    } else {
                        if ($row->processado !== 0) throw new Exception("Arquivo id " . $row->id . " com status de processamento não permite exclusão");
                    }
                }
            }
        } catch (\Throwable $th) {
            $ret->msg = $th->getMessage();
            return $ret->toJson();
        }

        try {
            foreach ($dataset as $row) {
                $errofile = null;
                $disk = Storage::disk($row->action === 1 ? 'local' : 'public');
                if ($disk->exists($row->storageurl)) $disk->delete($row->storageurl);
            }
        } catch (\Throwable $th) {
                $errofile = $th->getMessage();
                \Log::error('Erro ao deletar arquivo :: ' . $errofile);
        }


        try {
            DB::beginTransaction();

            FollowupFiles::whereIn('id', $ids)->delete();

            DB::commit();

            $ret->ok = true;
        } catch (\Throwable $th) {
            DB::rollBack();
            $ret->msg = $th->getMessage();
        }

        return $ret->toJson();
	}


    public function readplanilha($id,  $forceleiturafup = false)
    {
        $ret = new RetApiController;
        try {
            $iniciadobd = false;
            // $id = isset($request->id) ? $request->id : null;
            if (!$id) throw new Exception('ID do arquivo não foi informado');
            // $forceleiturafup = isset($request->forceleiturafup) ? boolval($request->forceleiturafup) : false;

            $starttime = microtime(true);
            $dataset = FollowupFiles::find($id);
            if (!$dataset) throw new Exception('Arquivo não foi encontrado');
            if ($dataset->processado === 1) throw new Exception('Arquivo já foi processado');
            if ($dataset->processado === 2) throw new Exception('Arquivo já esta em processamento, aguarde');
            if ($dataset->processado === 3) throw new Exception('Arquivo ja processado e contem erros');

            $checkoutroprocesso = FollowupFiles::where('processado', '=', 2)->first();
            if ($checkoutroprocesso) throw new Exception('Existe um outro processo em execução. Planilha id: ' . $checkoutroprocesso->id . ' - ' . $checkoutroprocesso->nomeoriginal);

            try {
                DB::beginTransaction();
                $dataset->processostart = Carbon::now();
                $dataset->processado = 2;//em processamento
                $dataset->save();
                DB::commit();
                $iniciadobd = true;
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new Exception('Erro ao salvar dados de inicio de processamento :: ' . $th->getMessage());
            }


            $disk = Storage::disk();
            $arquivo = $disk->get($dataset->storageurl);
            if (!$arquivo) throw new Exception('Nenhum arquivo encontrado');

            $import = new FollowupPlanilhaImport;
            $lista = Excel::toCollection($import, $dataset->storageurl, null, \Maatwebsite\Excel\Excel::XLSX);
            if (count($lista) === 1) $lista = $lista[0];

            $this->clientes = Cliente::whereNotNull('followupid')->get();
            $this->errosagendaall = FollowupErros::where('ativo', '=', 1)->where('tipo', '=', 'agenda')->get();
            $this->erroscoletaall = FollowupErros::where('ativo', '=', 1)->where('tipo', '=', 'coleta')->get();
            $this->errosdtpromessaall = FollowupErros::where('ativo', '=', 1)->where('tipo', '=', 'dtpromessa')->get();

            $nrow = 1;
            $error = [];
            $sucesso = 0;
            $novos = 0;
            foreach ($lista as $row) {
                // ignora primeira linha cabeçalho
                if ($nrow>1) {
                    $retProcesso = self::processaRow($row, $dataset->created_usuarioid, $forceleiturafup, $dataset->id);
                    if (!$retProcesso->ok) {
                        $error[] = [
                            'linha' => $nrow,
                            'erro' => utf8_encode($retProcesso->msg)
                        ];
                    } else {
                        if ($retProcesso->data['new']) $novos = $novos + 1;
                        $sucesso = $sucesso + 1;
                    }
                }
                $nrow = $nrow + 1;
            }
            $nrow = $nrow - 1;


            $data = [
                'processado' => intval($sucesso) + intval(count($error)),
                'sucesso' => $sucesso,
            ];
            if ($novos>0) $data['novos'] = $novos;
            if (count($error)>0) $data['error'] = $error;

            try {
                DB::beginTransaction();

                $endtime = microtime(true);
                $timediff = $endtime - $starttime;

                $dataset->processado = 1;
                $dataset->processoend = Carbon::now();
                $dataset->numlinhas = intval($data['processado']);
                $dataset->numerros = intval($error ? count($error) : 0);
                $dataset->log = (($dataset->numerros > 0) ? json_encode($error) : null);
                $dataset->tempoprocessamento = $timediff;
                $dataset->save();

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new Exception('Erro ao salvar dados do processamento :: ' . $th->getMessage());
            }

            $ret->data = $data;
            $ret->msg = $data['processado'] . ' linhas processadas';
            $ret->ok = true;
        } catch (\Throwable $th) {
            \Log::error('Erro no processamento de leitura da planilha :: ' . $th->getMessage());
            $ret->msg = $th->getMessage();

            try {
                DB::beginTransaction();
                $dataset->processoend = Carbon::now();
                $dataset->log = $th->getMessage();
                $dataset->processado = 3;//errro
                $dataset->save();
                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
            }
        }
        return $ret->toJson();
    }

    public function processaRow($row, $usuarioid = 0, $forceleiturafup  = false, $arquivoid)
    {
        $ret = new RetApiController;
        try {
            $followupid = $row[1];
            if (!$followupid) throw new Exception('Cliente não identificado. FollowUp ID não encontrado');

            $cliente = $this->clientes->firstWhere('followupid', $followupid);
            if (!$cliente) throw new Exception('Cliente não identificado. FollowUp ID não encontrado com o valor ' . $followupid);

            $cnpjforn = cleanDocMask($row[22]);
            if (strlen($cnpjforn) >= 15) $cnpjforn = substr($cnpjforn, 1, 14);

            $fornecedor = Cliente::where('cnpj', '=', $cnpjforn)->first();
            // cria fornecedor se não existir
            if (!$fornecedor) {
                try {
                    DB::beginTransaction();
                    $fornecedor = new Cliente();

                    $fornecedor->razaosocial = trim($row[20]);
                    $fornecedor->fantasia = $fornecedor->razaosocial;
                    $fornecedor->fantasia_followup = $fornecedor->razaosocial;
                    $fornecedor->cnpj = $cnpjforn;
                    $fornecedor->prazoentrega = 0;
                    $fornecedor->ativo = 1;
                    $fornecedor->created_usuarioid = 0;
                    $fornecedor->updated_usuarioid = 0;

                    $cidade = Cidades::where('ativo', '=', 1)->where('cidade', '=', trim(utf8_decode($row[23])))->where('uf', '=', trim($row[24]))->first();
                    if ($cidade) $fornecedor->cidadeid = $cidade->id;

                    $fornecedor->bairro = '**** IMPORTADO ****';
                    $fornecedor->endereco = '**** IMPORTADO ****';

                    $fornecedor->fone1 = trim(cleanDocMask($row[26]));
                    $fornecedor->fone1 = trim(cleanDocMask($row[26]));

                    $email = trim($row[27]); //pendente
                    // fornec.email_ordem = linha[27].ToString().ValorOuNulo();
                    // fornec.email_follow_up = fornec.email_ordem;
                    $fornecedor->save();
                    DB::commit();
                } catch (\Throwable $th) {
                    DB::rollBack();
                    throw new Exception('Erro ao cadastro fornecedor com o CNPJ ' . $cnpjforn  . ' :: ' . $th->getMessage());
                }
            }
            if (!$fornecedor) throw new Exception('Fornecedor não identificado com CNPJ ' . $cnpjforn);

            $ordemcompra = trim($row[7]);
            $ordemcompradig = trim($row[8]);
            if ($ordemcompradig == '-') $ordemcompradig = null;
            if ($ordemcompradig == '') $ordemcompradig = null;

            $requisicao = trim($row[2]);
            $itemnumerolinhapedido = trim($row[28]);
            if ($itemnumerolinhapedido == '-') $itemnumerolinhapedido = null;
            if ($itemnumerolinhapedido == '') $itemnumerolinhapedido = null;

            $followup = Followup::where('ordemcompra', '=', $ordemcompra)
                                ->where('requisicao', '=', $requisicao)
                                ->when(($ordemcompradig ? $ordemcompradig !== '' : false), function ($query) use ($ordemcompradig)  {
                                    return $query->Where('ordemcompradig', '=', $ordemcompradig);
                                })
                                ->when(($itemnumerolinhapedido ? $itemnumerolinhapedido !== '' : false), function ($query) use ($itemnumerolinhapedido)  {
                                    return $query->Where('itemnumerolinhapedido', '=', $itemnumerolinhapedido);
                                })
                                ->first();

            try {
                DB::beginTransaction();

                if ($followup) {
                    $new = false;
                } else {
                    $new = true;
                    $followup = new Followup();
                }


                $followup->arquivoultimoid = $arquivoid;
                $followup->updated_usuarioid = $usuarioid;
                $followup->clienteid = $cliente->id;
                $followup->fornecedorid = $fornecedor->id;
                $followup->requisicao = $requisicao;
                $followup->ordemcompra = $ordemcompra;
                if ($ordemcompradig) $followup->ordemcompradig = $ordemcompradig;
                if ($itemnumerolinhapedido) $followup->itemnumerolinhapedido = $itemnumerolinhapedido;

                $followup->comprador = trim($row[12]); //fup.comprador = linha[12].ToString().ValorOuNulo();
                $followup->fornecrazao = trim($row[20]); //fup.fornec_razao = linha[20].ToString().ValorOuNulo();
                $followup->forneccnpj = $cnpjforn;
                $followup->forneccidade = trim($row[23]); //fup.fornec_cidade = linha[23].ToString().ValorOuNulo();
                $followup->fornecuf = trim($row[24]); //fup.fornec_uf = linha[24].ToString().ValorOuNulo();
                $followup->fornectelefone = trim($row[26]); //fup.fornec_telefone = linha[26].ToString().ValorOuNulo();
                $followup->contato = trim($row[25]); //fup.contato = linha[25].ToString().ValorOuNulo();
                $followup->email = trim($row[27]); //fup.email = linha[27].ToString().ValorOuNulo();
                $followup->itemdescricao = trim($row[33]); //fup.item_descricao = linha[33].ToString();
                $followup->compradelegada = trim($row[4]); //fup.compra_delegada = linha[4].ToString();
                $followup->normalurgente = trim($row[5]); //fup.normal_urgente = linha[5].ToString();
                $followup->tipooc = trim($row[6]); //fup.tipo_oc = linha[6].ToString();
                $followup->statusoc = trim($row[9]); //fup.status_oc = linha[9].ToString();
                $followup->statusliberacao = trim($row[10]); //fup.status_liberacao = linha[10].ToString();
                $followup->situacaolinha = trim($row[11]); //fup.situacao_linha = linha[11].ToString();
                $followup->compradoracordo = trim($row[13]); //fup.comprador_acordo = linha[13].ToString();
                $followup->grupo = trim($row[29]); //fup.grupo = linha[29].ToString();
                $followup->familia = trim($row[30]); //fup.familia = linha[30].ToString();
                $followup->subfamilia = trim($row[31]); //fup.subfamilia = linha[31].ToString();
                $followup->udm = trim($row[35]); //fup.udm = linha[35].ToString();
                $followup->moeda = trim($row[40]); //fup.moeda = linha[40].ToString();
                $followup->diaatraso = intval(trim($row[19])); //if (!string.IsNullOrEmpty(linha[19].ToString())) { fup.dia_atraso = linha[19].ToInt(); }


                $dt = transformDate(trim($row[3]), 'd/m/Y');
                if ($dt) $followup->aprovacaorc = $dt; // if (linha[3].ToString() != "-") fup.aprovacao_rc = DateTime.Parse(linha[3].ToString());

                $dt = transformDate(trim($row[15]), 'd/m/Y');
                if ($dt) $followup->criacaooc = $dt; // if (linha[15].ToString().Trim() != "") fup.criacao_oc = DateTime.Parse(linha[15].ToString());

                $dt = transformDate(trim($row[17]), 'd/m/Y');
                if ($dt) $followup->dataliberacao = $dt; // if (linha[17].ToString() != "-") fup.data_liberacao = DateTime.Parse(linha[17].ToString());

                $v = trim($row[39]);
                if ($v !== '-') $followup->vlrultcompra = floatVal($v); //if (linha[39].ToString() != "-") fup.vlr_ult_compra = Double.Parse(linha[39].ToString());

                $dt = transformDate(trim($row[42]), 'd/m/Y');
                if ($dt) $followup->dataultimaentrada = $dt; //if ((linha[42].ToString() != "-") && (linha[42].ToString().Trim() != "")) fup.data_ultima_entrada = DateTime.Parse(linha[42].ToString());


                $followup->qtdesolicitada = floatVal(trim($row[34])); //linhaNova.qtdade_solicitada = Double.Parse(linha[34].ToString());
                $followup->qtderecebida = floatVal(trim($row[36])); //linhaNova.qtdade_recebida = Double.Parse(linha[36].ToString());
                $followup->qtdedevida = floatVal(trim($row[37])); //linhaNova.qtdade_devida = Double.Parse(linha[37].ToString());
                $followup->vlrunitario = floatVal(trim($row[38])); //linhaNova.vlr_unitario = Double.Parse(linha[38].ToString());
                $followup->totallinhaoc = floatVal(trim($row[41])); //linhaNova.total_linha_oc = Double.Parse(linha[41].ToString());


                $dt = transformDate(trim($row[18]), 'd/m/Y');
                $followup->datapromessa = $dt; //linhaNova.data_promessa = DateTime.Parse(linha[18].ToString());

                $dt = transformDate(trim($row[16]), 'd/m/Y');
                $followup->aprovacaooc = $dt; //linhaNova.aprovacao_oc = DateTime.Parse(linha[16].ToString());

                $dt = transformDate(trim($row[14]), 'd/m/Y');
                if ($dt) $followup->datanecessidaderc = $dt; //if (linha[14].ToString().Trim() != "") linhaNova.data_necessidade_rc = DateTime.Parse(linha[14].ToString());

                $followup->dhimportacao = Carbon::now(); //linhaNova.data_hora_importacao = DateTime.Now;
                $followup->datahoralancamento = Carbon::now(); //linhaNova.data_hora_lancamento = DateTime.Now;

                $followup->condpagto = trim($row[21]); //linhaNova.cond_pagto = linha[21].ToString();
                $followup->tipofrete = trim($row[43]); //linhaNova.tipo_frete = linha[43].ToString();
                $followup->observacao = trim($row[57]); //linhaNova.observacao = linha[57].ToString().Trim();
                $followup->notafiscal = trim($row[56]); //linhaNova.nota_fiscal = linha[56].ToString().Trim();
                $followup->itemid = trim($row[32]); //linhaNova.item_id = linha[32].ToString();


                //forçar leitura das datas de solic, agend e conf. na planilha
                //da Adecoagro e atualizar FUP. por padrão, não são atualizadas quando vazias.
                if (($forceleiturafup) || ($new))  {
                    $dt = transformDate(trim($row[46]), 'd/m/Y');
                    $followup->datasolicitacao = $dt; //DateTime.Parse(linha[46].ToString().Trim());

                    $dt = transformDate(trim($row[47]), 'd/m/Y');
                    $followup->dataagendamentocoleta = $dt; //DateTime.Parse(linha[47].ToString().Trim());

                    $dt = transformDate(trim($row[51]), 'd/m/Y');
                    $followup->dataconfirmacao = $dt; //DateTime.Parse(linha[51].ToString().Trim());
                } else {
                    $dt = transformDate(trim($row[46]), 'd/m/Y');
                    if ($dt) $followup->datasolicitacao = $dt; //DateTime.Parse(linha[46].ToString().Trim());

                    $dt = transformDate(trim($row[47]), 'd/m/Y');
                    if ($dt) $followup->dataagendamentocoleta = $dt; //DateTime.Parse(linha[47].ToString().Trim());

                    $dt = transformDate(trim($row[51]), 'd/m/Y');
                    if ($dt) $followup->dataconfirmacao = $dt; //DateTime.Parse(linha[51].ToString().Trim());
                }

                //InicialNaoUsar=0, FollowUp=1, Importacao=2
                $followup->tipoorigem = 2; // linhaNova.tipo_origem = (int)TipoOrigem.Importacao;

                //Iniciativa agendamento
                $chk_inicio_followup =  \mb_strtoupper(trim($row[48])); //linha[48].ToString().Trim().ToUpper();
                //0=Sem status, 1=Conecta, 2=Fornecedor
                $followup->iniciofollowup = '0';
                if ($chk_inicio_followup === 'FOLLOW-UP') $followup->iniciofollowup = '1';
                if ($chk_inicio_followup === 'FORNECEDOR') $followup->iniciofollowup = '2';

                //Erro de agendamento
                //0=Sem status, 1=OK, 2=Erro
                $chk_status_erro_agendamento = \mb_strtoupper(trim($row[44])); //linha[44].ToString().Trim().ToUpper();
                $followup->erroagendastatus = '0';
                if ($chk_status_erro_agendamento === 'OK')  $followup->erroagendastatus = '1';
                if ($chk_status_erro_agendamento === 'ERRO')  $followup->erroagendastatus = '2';

                if ($followup->erroagendastatus === '2') {
                    $errodesc = \mb_strtoupper(trim($row[45])); //linhaNova.id_followup_erro_agendamento = PegaIdErro(linha[45].ToString().Trim(), MotivoErro.Agendamento);
                    $errocad = $this->errosagendaall->firstWhere('descricao', $errodesc);
                    $followup->erroagendaid = ($errocad ? $errocad->id : null);
                } else {
                    $followup->erroagendaid = null;
                }
                //Erro de agendamento


                //Erro data da promessa
                //0=Sem status, 1=OK, 2=Erro
                $chk_status_erro_dt_promessa = \mb_strtoupper(trim($row[49])); //chk_status_erro_dt_promessa = linha[49].ToString().Trim().ToUpper();
                $followup->errodtpromessastatus = '0';
                if ($chk_status_erro_dt_promessa === 'OK')  $followup->errodtpromessastatus = '1';
                if ($chk_status_erro_dt_promessa === 'ERRO')  $followup->errodtpromessastatus = '2';

                if ($followup->errodtpromessastatus === '2') {
                    $errodesc = \mb_strtoupper(trim($row[50])); //linhaNova.id_followup_erro_dt_promessa = PegaIdErro(linha[50].ToString().Trim(), MotivoErro.Promessa);
                    $errocad = $this->errosdtpromessaall->firstWhere('descricao', $errodesc);
                    $followup->errodtpromessaid = ($errocad ? $errocad->id : null);
                } else {
                    $followup->errodtpromessaid = null;
                }
                //Erro data da promessa

                //Erro de coleta
                //0=Sem status, 1=OK, 2=Erro
                $chk_status_erro_coleta = \mb_strtoupper(trim($row[53])); //chk_status_erro_coleta = linha[53].ToString().Trim().ToUpper();
                $followup->errocoletastatus = '0';
                if ($chk_status_erro_coleta === 'OK')  $followup->errocoletastatus = '1';
                if ($chk_status_erro_coleta === 'ERRO')  $followup->errocoletastatus = '2';

                if ($followup->errocoletastatus === '2') {
                    $errodesc = \mb_strtoupper(trim($row[54])); //linhaNova.id_followup_erro_coleta = PegaIdErro(linha[54].ToString().Trim(), MotivoErro.Coleta);
                    $errocad = $this->erroscoletaall->firstWhere('descricao', $errodesc);
                    $followup->errocoletaid = ($errocad ? $errocad->id : null);
                } else {
                    $followup->errocoletaid = null;
                }
                //Erro de coleta

                $followup->save();


                try {
                    $logresult = $followup->registerLog(($new ? 2 : 3), $usuarioid, false); //1=Alteração manual operador, 2=Novo registro importação planilha, 3=update registro importação planilha
                } catch (\Throwable $th) {
                    \Log::error('RegisterLog :: ' . $th->getMessage());
                }


                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                throw new Exception('Erro ao inserir registro :: ' . $th->getMessage());
            }

            $ret->data = [
                'new' => $new
            ];
            $ret->id = $followup->id;
            $ret->ok = true;
        } catch (\Throwable $th) {
            \Log::error('Erro no processarow :: ' . $th->getMessage());
            $ret->msg = $th->getMessage();
        }
        return $ret;
    }
}
