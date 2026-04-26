<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessee extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function gov()
    {
        return $this->belongsTo(Gov::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
