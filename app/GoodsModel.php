<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GoodsModel extends Model
{
    protected $table = "p_goods";
    protected $primaryKey = 'goods_id';
    public $timestamps = false;
}
