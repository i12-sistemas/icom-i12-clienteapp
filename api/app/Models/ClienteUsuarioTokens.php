<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClienteUsuarioTokens extends Model
{
    use SoftDeletes;

    protected $table = 'clienteusuariotokens';
    protected $dates = ['deleted_at', 'created_at', 'updated_at', 'expire_at'];
    protected $guarded = ['token', 'accesscode'];

    public function clienteusuario()
    {
        return $this->hasOne(ClienteUsuario::class, 'id', 'clienteusuarioid');
    }
}
