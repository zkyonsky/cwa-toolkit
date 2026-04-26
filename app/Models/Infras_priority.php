<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Infras_priority extends Model
{
    protected $table = 'infras_priorities';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }
}
