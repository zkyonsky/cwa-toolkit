<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt_service extends Model
{
    protected $table = 'debt_services';
    protected $primaryKey = 'id';
    protected $fillable = [
        'assessment_id',
        'plafond',
        'tenor',
        'disbursement_period',
        'interest',
        'interest_rate',
        'first_disbursement',
        'grace_period',
        'avg_annual_return',
        'avg_annual_interest',
        'avg_annual_cost',
        'advantage',
        'challenge',
        'debt_service_ratio',
        'debt_burden_ratio',
        'debt_service_percapita',
        'debt_burden_percapita',
        'infras_real',
        'fiscal_ratio',
        'year',
        'dscr',
        'limit',
        'max_loan',
        'avg_exis_payment',
        'loan_withdrawal_plus_os',
        'unappropiated_revenue'
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }




}
