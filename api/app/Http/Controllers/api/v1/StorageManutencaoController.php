<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Http;

use App\Http\Controllers\RetApiController;

class StorageManutencaoController extends Controller
{
    public function limpeza()
    {
      $ret = new RetApiController;
      try {
        $counter = 0;
        $disk = Storage::disk('public');

        $listadir = [
            [ 'dir' => 'temp', 'reterhoras' => 24],
            [ 'dir' => 'nfe', 'reterhoras' => 24],
            [ 'dir' => 'export', 'reterhoras' => 3],
        ];

        foreach ($listadir as $key => $item) {
            $dir = $item['dir'];
            $reterhoras = intval($item['reterhoras']);
            $files = $disk->allfiles($dir);
            $list = [];
            foreach ($files as $key => $file) {
                $time = $disk->lastModified($file);
                $time = Carbon::createFromTimestamp($time);
                $diff = Carbon::now()->diffInHours($time);
                if ($diff > $reterhoras) {
                    $list[] = $file;
                }
            }
            //exclui arquivos
            foreach ($list as $file) {
                $disk->delete($file);
            }
            foreach ($disk->directories($dir) as $directory) {
                $files = $disk->allfiles($directory);
                if (!$files) $disk->deleteDirectory($directory);
            }
            $counter = $counter + count($list);
        }

        $ret->msg = $counter . ' arquivos excluidos';
        $ret->ok = true;
      } catch (\Throwable $th) {
        $ret->msg = $th->getMessage();
      }
      return $ret->toJson();
    }
}
