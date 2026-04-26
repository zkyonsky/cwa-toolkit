<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Financing extends Model
{
    protected $table = 'financings';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function gov()
    {
        return $this->belongsTo(Gov::class);
    }
}
