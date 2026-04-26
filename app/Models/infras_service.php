<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Infras_service extends Model
{
    protected $table = 'infras_services';
    protected $primaryKey = 'id';
    protected $guarded = [];

    protected $casts = [
        'education' => 'array',
        'health' => 'array',
        'water' => 'array',
        'waste' => 'array',
        'it' => 'array',
        'agriculture' => 'array',
        'transport' => 'array',
        'electricity' => 'array',
        'sport_art_culture' => 'array',
        'tourism' => 'array',
        'food' => 'array',
        'commerce' => 'array',
        'road' => 'array',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }
}
