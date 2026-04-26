<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Economy_indicator extends Model
{
    protected $table = 'economy_indicators';
    protected $primaryKey = 'id';
    protected $fillable = [
        'gov_code',
        'poverty',
        'unemployment',
        'gdp_growth',
        'gdp_perkapita',
        'hdci',
        'gdp',
        'infras_real',
        'fiscal_ratio',
        'year',
    ];

    public function gov()
    {
        return $this->belongsTo(Gov::class, 'gov_code', 'code');
    }
}
