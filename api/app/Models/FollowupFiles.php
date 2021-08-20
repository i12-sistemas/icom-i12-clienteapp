<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FollowupFiles extends Model
{
    protected $table = 'followup_files';
    protected $dates = ['dataref', 'dataref2', 'created_at', 'processostart', 'processoend'];
    public $timestamps = false;

    public function export($complete = false)
    {
        $dados = $this->toArray();
        $dados['created_at'] = $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null;
        $dados['processostart'] = $this->processostart ? $this->processostart->format('Y-m-d H:i:s') : null;
        $dados['processoend'] = $this->processoend ? $this->processoend->format('Y-m-d H:i:s') : null;
        $dados['dataref'] = $this->dataref ? $this->dataref->format('Y-m-d') : null;
        $dados['dataref2'] = $this->dataref2 ? $this->dataref2->format('Y-m-d') : null;

        $dados['log'] = (($this->log ? $this->log !== '' : false) ? json_decode($this->log) : null);

        if (($this->action === 2) && ($this->processado === 1)) {
            $disk = Storage::disk('public');
            if ($disk->exists($this->storageurl)) $dados['link'] = $disk->url($this->storageurl);
        }

        $dados['created_usuario'] = $this->created_usuario ? $this->created_usuario->toObject(false) : null;
        unset($dados['created_usuarioid']);
        return $dados;
    }

    public function created_usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'created_usuarioid');
    }

    public function setnomeoriginalAttribute($value)
    {
        $this->attributes['nomeoriginal'] =  utf8_decode($value);
    }
    public function getnomeoriginalAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setstorageurlAttribute($value)
    {
        $this->attributes['storageurl'] =  utf8_decode($value);
    }
    public function getstorageurlAttribute($value)
    {
      return utf8_encode($value);
    }

    public function setlogAttribute($value)
    {
        $this->attributes['log'] =  utf8_decode($value);
    }
    public function getlogAttribute($value)
    {
      return utf8_encode($value);
    }
}
