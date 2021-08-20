<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MotoristaTokens extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';
    protected $table = 'motoristatokens';
    protected $dates = ['deleted_at', 'created_at', 'updated_at', 'expire_at'];
    protected $guarded = ['token'];
}
