<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Infras_conclusion extends Model
{
    protected $table = 'infras_conclusions';
    protected $primaryKey = 'id';
   
    protected $guarded = [];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }
}
