<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartModel extends Model
{
    protected $table = "cart";
    protected $primaryKey = 'id';
    public $timestamps = false;
}
