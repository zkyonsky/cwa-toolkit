<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    //
    protected $fillable = [
        'category',
        'name',
        'definition',
        'actions',
    ];

    public function challengeActions()
    {
        return $this->hasMany(ChallengeAction::class);
    }

    public function category()
    {
        return $this->belongsTo(ChallengeCategory::class);
    }
}
