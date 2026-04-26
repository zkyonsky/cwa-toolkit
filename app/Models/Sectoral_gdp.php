<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sectoral_gdp extends Model
{
    protected $table = 'sectoral_gdps';
    protected $primaryKey = 'id';
    protected $fillable = [
        'gov_code',
        'agriculture_forestry_fishery',
        'mining_quarrying',
        'processing_industry',
        'electricity_gas',
        'water_waste',
        'contruction',
        'trade_vehicle_repair',
        'transportation_warehousing',
        'acomodation_food_beverage',
        'information_communication',
        'finance_insurance',
        'real_estate',
        'company_service',
        'gov_adm_defense_sosial_security',
        'education_service',
        'health_social_service',
        'other_service',
        'year',
    ];


    public function gov()
    {
        return $this->belongsTo(Gov::class, 'gov_code', 'code');
    }
}
