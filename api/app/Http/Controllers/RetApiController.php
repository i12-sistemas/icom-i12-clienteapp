<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RetApiController extends Controller
{
  private  $ok;
  private  $msg;
  private  $id;
  private  $counters;
  private  $pagination;
  private  $data;
  private  $responsecode;

  function __construct()
  {
      $this->id = null;
      $this->counters = null;
      $this->ok = false;
      $this->collection = null;
      $this->msg = '';
      $this->data = null;
      $this->descending = false;
      $this->sortby = '';
      $this->responsecode = 200;
  }

  public function __get($name) {
      switch (strtolower($name)){
          case 'ok':
            return $this->ok;
            break;
          case 'msg':
            return $this->msg;
            break;
          case 'counters':
            return $this->counters;
            break;
          case 'data':
            return $this->data;
            break;
          case 'sortby':
            return $this->sortby;
            break;
          case 'descending':
            return $this->descending;
            break;
          case 'collection':
            return $this->collection;
            break;
          case 'id':
            return $this->id;
            break;
          case 'responsecode':
            return $this->responsecode;
            break;
      }
  }

  public function __set($name, $value) {
       switch(strtolower($name)){
          case 'ok':
            $this->ok = $value;
            break;
          case 'msg':
            $this->msg = $value;
            break;
          case 'counters':
            $this->counters = $value;
            break;
          case 'data':
            $this->data = $value;
            break;
          case 'collection':
            $this->collection = $value;
            break;
          case 'sortby':
              $this->sortby = $value;
              break;
          case 'descending':
            $this->descending = $value;
            break;
          case 'id':
            $this->id = $value;
            break;
          case 'responsecode':
            $this->responsecode = $value;
            break;
        }
  }

  public function toJson(){

    //   $debugmode = \Config::get('app.debug') ;
      $debugmode = false ;
      if ($debugmode) {
        $dispositivo = session('dispositivo');
        $usuario = session('usuario');
        $empresa = session('empresa');
      }

      if(!$this->collection){
          $dados = $this->data;

      }else{
          $classname = get_class($this->collection);
          //"Illuminate\Database\Eloquent\Collection"
          if (!$this->data) {
            $d = [];
            foreach ($this->collection as $value) {
              $d[] = $value;
            }
            $this->data = $d;
          }
          $dados = [
              'total'         =>      ($classname == "Illuminate\Pagination\LengthAwarePaginator") ? $this->collection->total() : $this->collection->count(),
              'current_page'  =>      ($classname == "Illuminate\Pagination\LengthAwarePaginator") ? $this->collection->currentPage() : 1,
              'per_page'      =>      ($classname == "Illuminate\Pagination\LengthAwarePaginator") ? $this->collection->perPage() : $this->collection->count(),
              'last_page'     =>      ($classname == "Illuminate\Pagination\LengthAwarePaginator") ? $this->collection->lastPage() : 1,
              'descending'    =>      ($classname == "Illuminate\Pagination\LengthAwarePaginator") ? ($this->descending == 'desc' ? 'desc' : 'asc') : 'asc',
              'sortby'        =>      ($classname == "Illuminate\Pagination\LengthAwarePaginator") ? ($this->sortby ? $this->sortby : '')  : '',
              'last_page'     =>      ($classname == "Illuminate\Pagination\LengthAwarePaginator") ? $this->collection->lastPage() : 1,
              'rows'          =>      $this->data
              ];

            if ($this->counters) $dados["counters"] = $this->counters ;
      }
      $ret = [ "ok" => $this->ok ];
      if($this->msg!=='') $ret["msg"] =  $this->msg ;
      if(isset($this->id))
        if($this->id!=='')
          $ret["id"] =  $this->id ;
      if(isset($dados))
          $ret["data"] =  $dados ;

      // security somente pra validar sessão logada, pode comentar
      if ($debugmode) {
        $security = [];
        if ($dispositivo) $security[] = ['dispositivo' => $dispositivo];
        if ($usuario) $security[] = ['usuario' => $usuario];
        if ($empresa) $security[] = ['empresa' => $empresa];
        if ($security)
          if(count($security)>0)
            $ret["security"] = $security ;
        }

      return response()->json($ret, $this->responsecode);
  }
}
