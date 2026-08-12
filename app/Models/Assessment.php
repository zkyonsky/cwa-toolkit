<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assessment extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function assessee()
    {
        return $this->belongsTo(Assessee::class);
    }

    public function infrasService()
    {
        return $this->hasOne(infras_service::class, 'assessment_id');
    }

    public function infrasPriorities()
    {
        return $this->hasMany(Infras_priority::class, 'assessment_id');
    }

    public function infrasConclusion()
    {
        return $this->hasOne(Infras_conclusion::class, 'assessment_id');
    }

    public function debtService()
    {
        return $this->hasOne(Debt_service::class, 'assessment_id');
    }

    public function economyIndicator()
    {
        return $this->hasOne(Economy_indicator::class, 'assessment_id');
    }

    public function sectoralGdp()
    {
        return $this->hasOne(Sectoral_gdp::class, 'assessment_id');
    }

    public function selfAssessment()
    {
        return $this->hasOne(Self_assessment::class, 'assessment_id');
    }

    public function actionPlans()
    {
        return $this->hasMany(ActionPlan::class, 'assessment_id');
    }
}
