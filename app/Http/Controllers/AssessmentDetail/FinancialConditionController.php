<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Financial_indicator;

class FinancialConditionController extends Controller
{
    public function edit(Request $request, Assessment $assessment)
    {
        $assessment->load(['assessee.gov', 'debtService']);
        $gov = $assessment->assessee->gov;
        if (!$gov)
            return redirect()->back()->with('error', 'Gov not found');

        $govName = $gov->name;
        $govLevel = $gov->level;

        $assessmentYear = date('Y', strtotime($assessment->date));
        $latestRealYear = $gov->budget_real()->max('year') ?? ($assessmentYear - 1);

        $endYear = $request->query('end_year', $latestRealYear);
        $startYear = $request->query('start_year', $endYear - 2);
        $years = range($startYear, $endYear);

        $budgetReals = $gov->budget_real()->whereIn('year', $years)->get()->keyBy('year');
        $sectoralGdps = $gov->sectoral_gdp()->whereIn('year', $years)->get()->keyBy('year');

        $financialData = [];
        foreach ($years as $year) {
            $br = $budgetReals->get($year);
            $gdp = $sectoralGdps->get($year);

            $totalGdp = 0;
            if ($gdp) {
                $totalGdp = $gdp->agriculture_forestry_fishery + $gdp->mining_quarrying + $gdp->processing_industry
                    + $gdp->electricity_gas + $gdp->water_waste + $gdp->contruction + $gdp->trade_vehicle_repair
                    + $gdp->transportation_warehousing + $gdp->acomodation_food_beverage + $gdp->information_communication
                    + $gdp->finance_insurance + $gdp->real_estate + $gdp->company_service
                    + $gdp->gov_adm_defense_sosial_security + $gdp->education_service
                    + $gdp->health_social_service + $gdp->other_service;
            }

            $prevBr = $gov->budget_real()->where('year', $year - 1)->first();
            $prevPad = $prevBr ? ($prevBr->pad_after_cleansing ?? 0) : 0;
            $currentPad = $br ? ($br->pad_after_cleansing ?? 0) : 0;
            $padGrowth = $prevPad > 0 ? (($currentPad - $prevPad) / $prevPad) * 100 : 0;

            $totalRevenue = $br ? ($br->income_after_cleansing ?? 0) : 0;
            $totalSpending = $br ? ($br->spending_after_cleansing ?? 0) : 0;
            $totalTransfer = $br ? ($br->total_transfer ?? 0) : 0;
            $opSpending = $br ? ($br->operational_spending ?? 0) : 0;
            $transferIncome = $br ? ($br->transfer_income ?? 0) : 0;
            $otherLegit = $br ? ($br->other_legitimate_income ?? 0) : 0;
            $capSpending = $br ? ($br->capital_spending ?? 0) : 0;
            $empSpending = $br ? ($br->employee_spending ?? 0) : 0;

            $financialData[$year] = [
                'bpk_opinion' => $br ? ($br->bpk_opinion ?? '') : '',
                'total_revenue' => $totalRevenue,
                'total_pad' => $currentPad,
                'pad_growth' => $padGrowth,
                'pad_ratio' => $totalRevenue > 0 ? ($currentPad / $totalRevenue) * 100 : 0,
                'transfer_ratio' => $totalRevenue > 0 ? ($transferIncome / $totalRevenue) * 100 : 0,
                'other_legit_ratio' => $totalRevenue > 0 ? ($otherLegit / $totalRevenue) * 100 : 0,
                'op_surplus_deficit_ratio' => $totalRevenue > 0 ? (($totalRevenue - $opSpending) / $totalRevenue) * 100 : 0,
                'surplus_deficit_before_fin' => $totalRevenue > 0 ? ((($totalRevenue - ($totalSpending + $totalTransfer))) / $totalRevenue) * 100 : 0,
                'cap_spending_ratio' => $totalSpending > 0 ? ($capSpending / $totalSpending) * 100 : 0,
                'emp_spending_ratio' => $totalSpending > 0 ? ($empSpending / $totalSpending) * 100 : 0,
                'total_gdp' => $totalGdp,
            ];
        }

        $financialIndicator = Financial_indicator::where('assessment_id', $assessment->id)->first()
            ?: new Financial_indicator([
                'assessment_id' => $assessment->id,
                'volatil_pad' => 0,
                'pad_last_three_year' => 0,
                'total_debt' => 0,
                'fiscal_capacity' => '',
            ]);

        return Inertia::render('assessmentDetail/EditFinancialCondition', [
            'assessment' => $assessment,
            'govName' => $govName,
            'govLevel' => $govLevel,
            'years' => $years,
            'financialData' => $financialData,
            'financialIndicator' => $financialIndicator,
            'debtService' => $assessment->debtService,
            'financing' => $gov->financing()->first(),
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $request->validate([
            'total_revenue' => 'nullable|numeric',
            'total_pad' => 'nullable|numeric',
            'pad_growth' => 'nullable|numeric',
            'pad_revenue' => 'nullable|numeric',
            'transfer_revenue' => 'nullable|numeric',
            'other_total_revenue' => 'nullable|numeric',
            'volatil_pad' => 'nullable|numeric',
            'operation_revenue' => 'nullable|numeric',
            'surplus_deficit_before_financing' => 'nullable|numeric',
            'capital_spending' => 'nullable|numeric',
            'employee_spending' => 'nullable|numeric',
            'pad_last_three_year' => 'nullable|numeric',
            'total_debt' => 'nullable|numeric',
            'debt_gdp' => 'nullable|numeric',
            'debt_revenue' => 'nullable|numeric',
            'ds_revenue' => 'nullable|numeric',
            'dscr' => 'nullable|numeric',
            'fiscal_capacity' => 'nullable|string',
            'bpk_opinions' => 'nullable|array',
            'bpk_opinions.*' => 'nullable|string',
        ]);

        if ($request->has('bpk_opinions') && is_array($request->bpk_opinions)) {
            $gov = $assessment->assessee->gov;
            if ($gov) {
                foreach ($request->bpk_opinions as $year => $opinion) {
                    if (!empty($opinion)) {
                        $gov->budget_real()->where('year', $year)->update(['bpk_opinion' => $opinion]);
                    }
                }
            }
        }

        Financial_indicator::updateOrCreate(
            ['assessment_id' => $assessment->id],
            $request->only([
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
                'fiscal_capacity'
            ])
        );

        return redirect()->route("assessment-details.economy-condition.edit", $assessment->id)->with("message", "Keuangan berhasil diisi!");
    }
}
