<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActionPlan extends Model
{
    protected $fillable = [
        'assessment_id',
        'conclusion',
        'challenge',
        'action_plan',
    ];
}
