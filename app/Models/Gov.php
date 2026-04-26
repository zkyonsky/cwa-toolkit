<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gov extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function assessees()
    {
        return $this->hasMany(Assessee::class);
    }

    public function budget_plan()
    {
        return $this->hasMany(Budget_plan::class, 'gov_code', 'code');
    }

    public function budget_real()
    {
        return $this->hasMany(Budget_real::class, 'gov_code', 'code');
    }

    public function economy_indicator()
    {
        return $this->hasMany(Economy_indicator::class, 'gov_code', 'code');
    }

    public function financing()
    {
        return $this->hasMany(Financing::class, 'gov_code', 'code');
    }

    public function infras_conclusion()
    {
        return $this->hasMany(Infras_conclusion::class, 'gov_code', 'code');
    }

    public function infras_priority()
    {
        return $this->hasMany(Infras_priority::class, 'gov_code', 'code');
    }

    public function infras_service()
    {
        return $this->hasMany(Infras_service::class, 'gov_code', 'code');
    }

    public function sectoral_gdp()
    {
        return $this->hasMany(Sectoral_gdp::class, 'gov_code', 'code');
    }
}
