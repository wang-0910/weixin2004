<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginModel extends Model
{
    protected $table = "user";
    protected $primaryKey = 'id';
    public $timestamps = false;
}
