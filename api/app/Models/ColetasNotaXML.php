<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Exception;
use Illuminate\Support\Facades\Storage;

class ColetasNotaXML extends Model
{
    protected $table = 'coletas_nota_xml';
    protected $dates = ['created_at'];
    public $timestamps = false;
    protected $hidden = ['fileblob', 'filestorage', 'filename', 'fileext', 'filesize', 'filemd5'];


    public function getfilenameAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfilenameAttribute($value)
    {
      $this->attributes['filename'] =  utf8_decode($value);
    }

    public function exportFileBD($strpath = 'nfe/xml')
    {
      try {
        if ($this->filename ? $this->filename === '' : false) return false;

        $disk = Storage::disk('public');

        $file = $strpath . '/' . $this->chave . '.xml';
        if ($disk->exists($file)) return true;

        if (!$disk->exists($strpath)) $disk->makeDirectory($strpath);
        $disk->put($file, $this->fileblob);

        return true;
      } catch (\Throwable $th) {
          throw new Exception('Falha ao exportar XML do banco de dados - ' . $th->getMessage());
      }
    }



    public function getfileextAttribute($value)
    {
      return utf8_encode($value);
    }
    public function setfileextAttribute($value)
    {
      $this->attributes['fileext'] =  utf8_decode($value);
    }

}
