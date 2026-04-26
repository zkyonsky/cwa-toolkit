<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Budget_real extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function gov()
    {
        return $this->belongsTo(Gov::class);
    }
}
