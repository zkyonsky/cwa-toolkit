<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeAction extends Model
{
    protected $fillable = [
        'challenge_id',
        'name',
    ];

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }
}
