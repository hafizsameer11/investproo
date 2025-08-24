<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    protected $fillable = ['type', 'address', 'status'];
      public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }
}
