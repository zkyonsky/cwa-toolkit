<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget_plan extends Model
{
    protected $table = 'budget_plans';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function gov()
    {
        return $this->belongsTo(Gov::class);
    }
    
}
