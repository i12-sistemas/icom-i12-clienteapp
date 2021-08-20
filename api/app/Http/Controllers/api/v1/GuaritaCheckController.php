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

use App\Models\Unidade;
use App\Models\GuaritaCheck;
use App\Models\GuaritaCheckItem;
use App\Models\ColetasNota;
use App\Enums\GuaritaCheckStatusEnumType;

class GuaritaCheckController extends Controller
{

    public function findMinhaGuarita(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $dataset = GuaritaCheck::where('userid', '=', $usuario->id)->where('status', '=', '1')->orderBy('created_at', 'ASC')->first();
        if (!$dataset) throw new Exception("Nenhuma guarita em aberto");

        $ret->data = $dataset->export(true);
        $ret->ok = true;

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

    public function saveMinhaGuarita(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $guarita = GuaritaCheck::where('userid', '=', $usuario->id)->where('status', '=', '1')->orderBy('created_at', 'ASC')->first();
        $action =  ($guarita ? $guarita->id > 0 : false) ? 'update' : 'add';


        $nfechaves = isset($request->nfechave) ? $request->nfechave : null;
        if ($action=='add') {
          if (!$nfechaves) throw new Exception("Nenhuma chave informada!");
          if (!is_array($nfechaves)) throw new Exception("Nenhuma chave válida informada!");
        }
        if ($nfechaves) {
          if (count($nfechaves) == 1) {
            $check = GuaritaCheckItem::where('nfechave', '=', $nfechaves[0])->first();
            if ($check) throw new Exception("Chave já foi inserida anteriormente!");

            if ( $action == 'update') {
              if ($guarita->motoristaid > 0) {
                $check = ColetasNota::where('notachave', '=', $nfechaves[0])->first();
                if ($check) {
                  if ($guarita->motoristaid !== $check->motoristaid)
                    throw new Exception("Motorista da guarita difere do motorista que deu entrada na nota [ " . $check->motorista->nome .  " ]");
                }
              }
            }

          }
        }

        $chavesvalidas = [];
        if ($nfechaves) {
          foreach ($nfechaves as $chave) {
            if (testaChaveNFe($chave)) $chavesvalidas[] = $chave;
          }
        }
        if ($action=='add') {
          if (!$chavesvalidas) throw new Exception("Nenhuma chave válida informada!");
          if (count($chavesvalidas) <= 0) throw new Exception("Nenhuma chave válida informada!");
        }

        $unidadeid = isset($request->unidadeid) ? intVal($request->unidadeid) : null;
        $motoristaid = isset($request->motoristaid) ? intVal($request->motoristaid) : null;
        $veiculoid = isset($request->veiculoid) ? intVal($request->veiculoid) : null;
        if ($action == 'add') {
          if (!($unidadeid > 0)) throw new Exception("Unidade não foi informada");
          $unidade = Unidade::find($unidadeid);
          if (!$unidade) throw new Exception("Nenhuma unidade encontrada");
        }

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        if ($action=='add') {
          $guarita = new GuaritaCheck();
          $guarita->status = GuaritaCheckStatusEnumType::EmAberto;
          $guarita->unidadeid = $unidade->id;
          $guarita->userid = $usuario->id;
          $guarita->erroqtde = 0;
          $guarita->motoristalock = false;
        }
        if ($request->has('motoristaid')) $guarita->motoristaid = $motoristaid;
        if ($request->has('veiculoid')) $guarita->veiculoid = $veiculoid;
        $upd = $guarita->save();


        foreach ($chavesvalidas as $chave) {
          $item = new GuaritaCheckItem();
          $item->guaritacheckid = $guarita->id;
          $item->nfechave = $chave;
          $item->userid = $usuario->id;
          $item->created_at = Carbon::now();
          $upd = $item->save();

          if (!$guarita->motoristaid) {
            if (!$item->erro) {
              if ($item->coletanota ? $item->coletanota->motoristaid > 0 : false) {
                $guarita->motoristaid = $item->coletanota->motoristaid;
                $guarita->veiculoid = $item->coletanota->motorista->veiculoid > 0 ? $item->coletanota->motorista->veiculoid : null;
                $upd = $guarita->save();
              }
            }
          }
        }

        $guarita->totaliza();
        $upd = $guarita->save();

        DB::commit();

        $ret->id = $guarita->id;
        $ret->data = $guarita->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }


    public function deleteMinhaGuarita(Request $request)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $guarita = GuaritaCheck::where('userid', '=', $usuario->id)->where('status', '=', '1')->orderBy('created_at', 'ASC')->first();
        if (!$guarita) throw new Exception("Nenhuma guarita encontrada!");
        if ($guarita->status !== GuaritaCheckStatusEnumType::EmAberto) throw new Exception("Status da guarita não permite alteração!");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        $upd = GuaritaCheckItem::where('guaritacheckid', '=', $guarita->id)->delete();
        $upd = $guarita->delete();

        DB::commit();

        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }

    public function deleteItensMinhaGuarita(Request $request)
    {
      $ret = new RetApiController;
      try {

        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $guarita = GuaritaCheck::where('userid', '=', $usuario->id)->where('status', '=', '1')->orderBy('created_at', 'ASC')->first();
        if (!$guarita) throw new Exception("Nenhuma guarita encontrada!");
        if ($guarita->status !== GuaritaCheckStatusEnumType::EmAberto) throw new Exception("Status da guarita não permite alteração!");

        $chaves = isset($request->chaves) ? $request->chaves : null;
        $itens = GuaritaCheckItem::where('guaritacheckid', '=', $guarita->id)->whereIn('nfechave', $chaves)->get();
        if (!$itens) throw new Exception("Nenhum item encontrado com a chave informada");

      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        foreach ($itens as $item) {
          $upd = $item->delete();
        }

        $guarita->totaliza();
        $upd = $guarita->save();

        DB::commit();

        $ret->id = $guarita->id;
        $ret->data = $guarita->export(true);
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }


      return $ret->toJson();
    }


    public function encerrarMinhaGuarita(Request $request)
    {
      $ret = new RetApiController;
      try {
        $usuario = session('usuario');
        if (!$usuario) throw new Exception('Nenhum usuário autenticado');

        $guarita = GuaritaCheck::where('userid', '=', $usuario->id)->where('status', '=', '1')->orderBy('created_at', 'ASC')->first();
        if (!$guarita) throw new Exception("Nenhuma guarita encontrada!");
        if ($guarita->status !== GuaritaCheckStatusEnumType::EmAberto) throw new Exception("Status atual não permite encerrar!");
        if (!$guarita->itens) throw new Exception("Nenhum item lançado. Se necessário exclua a guarita");
        if (count($guarita->itens) <= 0) throw new Exception("Nenhum item lançado. Se necessário exclua a guarita");
        if (!$guarita->motoristaid) throw new Exception("Obrigatório informar o motorista");
        if (!$guarita->veiculoid) throw new Exception("Obrigatório informar o veículo");

        foreach ($guarita->itens as $key => $item) {
          if (!$item->erro) {
            if ($item->coletanota ? $item->coletanota->id > 0 : false) {
                if ($item->coletanota->motoristaid !== $guarita->motoristaid)
                throw new Exception("Nota #" . $item->nfenumero . " está relacionada a um motorista diferente da conferência [ " . $item->coletanota->motorista->nome . " ]");
            }
          }
        }


      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
        return $ret->toJson();
      }

      try {
        DB::beginTransaction();

        foreach ($guarita->itens as $key => $item) {
          if ($item->erro) {
            $docfiscal = 'nfe';
            //check if exists
            $coletanota = ColetasNota::where('notachave', '=', $item->nfechave)->first();

            if ($coletanota) {
              $item->erro = 0;
              $item->erromsg = null;
              $item->save();
            } else {
              $coletanota = new ColetasNota;
              $coletanota->guarita = 1;
              $coletanota->guaritauserid = $usuario->id;
              $coletanota->guaritaid = $guarita->id;
              $coletanota->guaritaitemid = $item->id;
              $coletanota->uuid = null;
              $coletanota->localid  = null;
              $coletanota->dhlocal_data = Carbon::now();
              $coletanota->dhlocal_created_at = Carbon::now();
              $coletanota->coletaavulsaincluida = 0;
              $coletanota->coletaavulsa = 1;
              $coletanota->idcoletaavulsa = null;
              $coletanota->idcoleta = null;
              $coletanota->motoristaid = $guarita->motoristaid;
              $coletanota->notanumero = $item->nfenumero;
              $coletanota->docfiscal = $docfiscal;
              $coletanota->notachave = $item->nfechave === '' ? null : $item->nfechave;
              $coletanota->save();
            }
          }
        }

        $guarita->totaliza();
        $guarita->status = GuaritaCheckStatusEnumType::Encerrado;
        $upd = $guarita->save();

        DB::commit();

        $ret->id = $guarita->id;
        $ret->ok = true;
      } catch (\Throwable $th) {
        DB::rollBack();
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

}
