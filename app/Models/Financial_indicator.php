<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Financial_indicator extends Model
{
    protected $table = 'financial_indicators';
    protected $primaryKey = 'id';
    protected $fillable = [
        'assessment_id',
        'total_revenue',
        'total_pad',
        'pad_growth',
        'pad_revenue',
        'transfer_revenue',
        'other_total_revenue',
        'volatil_pad',
        'operation_revenue',
        'surplus_deficit_before_financing',
        'capital_spending',
        'employee_spending',
        'pad_last_three_year',
        'total_debt',
        'debt_gdp',
        'debt_revenue',
        'ds_revenue',
        'dscr',
        'fiscal_capacity',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
