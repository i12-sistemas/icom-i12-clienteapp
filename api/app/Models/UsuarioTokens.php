<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuarioTokens extends Model
{
    use SoftDeletes;

    protected $table = 'usuariotokens';
    protected $dates = ['deleted_at', 'created_at', 'updated_at', 'expire_at'];
    protected $guarded = ['token'];

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'login', 'username');
    }
}
