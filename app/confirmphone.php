<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class confirmphone extends Model
{
    protected $table="confirmpassword";
  
    protected $fillable = [
        'telephone', 'code','statut'
    ];
}
