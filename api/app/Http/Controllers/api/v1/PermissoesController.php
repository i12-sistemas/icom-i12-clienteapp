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

use App\Models\Permissoes;

class PermissoesController extends Controller
{
    private $permissoesAll = null;


    public function getChildren(Permissoes $pai) {
        if (!$pai) return;
        if (!$pai->grupo) return;


        $dados = [];
        foreach ($this->permissoesAll as $row) {
            if ($row->idpai === $pai->id) {
                $item = $row->toNodeTree();
                if ($row->grupo == 1) {
                    $children = $this->getChildren($row);
                    if ($children) $item['children'] = $children;
                }
                $dados[] = $item;
            }
        }
        if (count($dados) == 0) $dados = null;
        return $dados;
    }

    public function listall(Request $request)
    {
      $ret = new RetApiController;
      try {
        $this->permissoesAll = Permissoes::orderBy('ordem', 'asc')->orderBy('id', 'asc')->get();
        $dataset = Permissoes::whereNull('idpai')->orderBy('ordem', 'asc')->orderBy('id', 'asc')->get();

        $dados = [];
        foreach ($dataset as $row) {
            $item = $row->toNodeTree();
            if ($row->grupo == 1) {
                $children = $this->getChildren($row);
                if ($children) $item['children'] = $children;
            }
            $dados[] = $item;
        }
        $ret->data = $dados;
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }

}
