<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Budget_real;

class BudgetRealController extends Controller
{
    public function edit(Assessment $assessment)
    {
        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;
        if (!$gov)
            return redirect()->back()->with('error', 'Gov not found');

        $assessmentYear = date('Y', strtotime($assessment->date));
        $latestYear = $gov->budget_real()->max('year') ?? ($assessmentYear - 1);
        $years = range($latestYear - 4, $latestYear);

        $budgetReals = $gov->budget_real()
            ->whereIn('year', $years)
            ->get()
            ->keyBy('year');

        $data = [];
        foreach ($years as $year) {
            $data[$year] = $budgetReals->get($year) ?: new Budget_real([
                'gov_code' => $gov->code,
                'year' => $year,
                'bpk_opinion' => '',
                'input_status' => 'Draft',
            ]);
        }

        return Inertia::render('assessmentDetail/EditBudgetReal', [
            'assessment' => $assessment,
            'budgetReals' => $data,
            'years' => $years,
            'latestYear' => $latestYear
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;

        $request->validate([
            'budgetReals' => 'required|array',
        ]);

        \Log::info('Updating Budget Reals for Gov: ' . $gov->code, [
            'years' => array_keys($request->budgetReals),
            'data' => $request->budgetReals
        ]);

        foreach ($request->budgetReals as $year => $data) {
            $gov->budget_real()->updateOrCreate(
                ['year' => $year],
                [
                    'bpk_opinion' => $data['bpk_opinion'] ?? '',
                    'input_status' => $data['input_status'] ?? 'Draft',
                    'income_after_cleansing' => $data['income_after_cleansing'] ?? 0,
                    'pad_after_cleansing' => $data['pad_after_cleansing'] ?? 0,
                    'tax_income' => $data['tax_income'] ?? 0,
                    'retribution_income' => $data['retribution_income'] ?? 0,
                    'asset_income' => $data['asset_income'] ?? 0,
                    'other_pad' => $data['other_pad'] ?? 0,
                    'transfer_income' => $data['transfer_income'] ?? 0,
                    'other_legitimate_income' => $data['other_legitimate_income'] ?? 0,
                    'other_income' => $data['other_income'] ?? 0,
                    'spending_after_cleansing' => $data['spending_after_cleansing'] ?? 0,
                    'operational_spending' => $data['operational_spending'] ?? 0,
                    'employee_spending' => $data['employee_spending'] ?? 0,
                    'good_service_spending' => $data['good_service_spending'] ?? 0,
                    'interest_spending' => $data['interest_spending'] ?? 0,
                    'subsidy_spending' => $data['subsidy_spending'] ?? 0,
                    'grant_spending' => $data['grant_spending'] ?? 0,
                    'social_spending' => $data['social_spending'] ?? 0,
                    'capital_spending' => $data['capital_spending'] ?? 0,
                    'land_spending' => $data['land_spending'] ?? 0,
                    'machine_spending' => $data['machine_spending'] ?? 0,
                    'building_spending' => $data['building_spending'] ?? 0,
                    'infrastructure_spending' => $data['infrastructure_spending'] ?? 0,
                    'other_fix_asset_spending' => $data['other_fix_asset_spending'] ?? 0,
                    'other_asset_spending' => $data['other_asset_spending'] ?? 0,
                    'unexpected_spending' => $data['unexpected_spending'] ?? 0,
                    'total_transfer' => $data['total_transfer'] ?? 0,
                ]
            );
        }

        return redirect()->back()->with('message', 'Budget Real data updated successfully!');
    }
}
