<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $fillable = ['product', 'user_id','note','date','status'];

    public function user() {
        return $this->belongsTo('App\User');
    }

}
