<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Assessee;
use App\Models\Budget_plan;
use App\Models\Budget_real;
use App\Models\Gov;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $govs = Gov::orderBy('name')->get(['code', 'name']);
        $selectedGovCode = $request->query('gov_code');

        if ($user->hasRole('User')) {
            $assessee = Assessee::with('gov')->where('user_id', $user->id)->first();
            if ($assessee && $assessee->gov) {
                $selectedGovCode = $assessee->gov->code;
                $govs = Gov::where('code', $selectedGovCode)->get(['code', 'name']);
            } else {
                // User has no assessee record, restrict them
                $selectedGovCode = null;
                $govs = collect();
            }
        } elseif (!$selectedGovCode) {
            $aceh = Gov::where('name', 'Aceh')->first();
            $selectedGovCode = $aceh ? $aceh->code : ($govs->first()->code ?? null);
        }

        $budgetReals = Budget_real::where('gov_code', $selectedGovCode)
            ->orderBy('year')
            ->get();

        $processedData = $this->processChartData($budgetReals);

        // Fetch user's assessments if role is User
        $userAssessments = [];
        if ($user->hasRole('User')) {
            $assessee = Assessee::where('user_id', $user->id)->first();
            if ($assessee) {
                $userAssessments = Assessment::where('assessee_id', $assessee->id)
                    ->select('id', 'assessee_id', 'date', 'economy_condition', 'financial_condition', 'indicative_rating', 'final_rating')
                    ->with('assessee.gov:code,name')
                    ->orderBy('date', 'desc')
                    ->get();
            }
        }

        // Fetch admin stats for non-User roles
        $adminStats = null;
        if (!$user->hasRole('User')) {
            $totalAssessedPemda = Assessment::distinct('assessee_id')->count('assessee_id');
            $totalMemadaiPemda = Assessment::where('final_rating', 'Memadai')
                ->distinct('assessee_id')
                ->count('assessee_id');
            $latestBudgetRealYear = Budget_real::max('year');
            $latestBudgetPlanYear = Budget_plan::max('year');

            $adminStats = [
                'totalAssessedPemda' => $totalAssessedPemda,
                'totalMemadaiPemda' => $totalMemadaiPemda,
                'latestBudgetRealYear' => $latestBudgetRealYear,
                'latestBudgetPlanYear' => $latestBudgetPlanYear,
            ];
        }

        return Inertia::render('Dashboard', [
            'govs' => $govs,
            'selectedGovCode' => $selectedGovCode,
            'chartData' => $processedData,
            'userAssessments' => $userAssessments,
            'adminStats' => $adminStats,
        ]);
    }

    private function processChartData($budgetReals)
    {
        $years = [];
        $padData = [];
        $padGrowth = [];
        $autonomyData = [
            'pad' => [],
            'transfer' => [],
            'other' => []
        ];
        $spendingData = [
            'capital' => [],
            'employee' => []
        ];
        $surplusData = [
            'operating' => [],
            'net' => []
        ];

        $prevPad = null;

        foreach ($budgetReals as $real) {
            $years[] = $real->year;

            // PAD & Growth
            $padData[] = $real->pad_after_cleansing;
            if ($prevPad !== null && $prevPad > 0) {
                $growth = (($real->pad_after_cleansing / $prevPad) - 1) * 100;
                $padGrowth[] = round($growth, 2);
            } else {
                $padGrowth[] = 0;
            }
            $prevPad = $real->pad_after_cleansing;

            // Autonomy (as % of income_after_cleansing)
            $totalIncome = $real->income_after_cleansing ?: 1;
            $autonomyData['pad'][] = round(($real->pad_after_cleansing / $totalIncome) * 100, 2);
            $autonomyData['transfer'][] = round(($real->transfer_income / $totalIncome) * 100, 2);
            $autonomyData['other'][] = round(($real->other_legitimate_income / $totalIncome) * 100, 2);

            // Spending (as % of spending_after_cleansing)
            $totalSpending = $real->spending_after_cleansing ?: 1;
            $spendingData['capital'][] = round(($real->capital_spending / $totalSpending) * 100, 2);
            $spendingData['employee'][] = round(($real->employee_spending / $totalSpending) * 100, 2);

            // Surplus/Deficit
            $surplusData['operating'][] = round($real->income_after_cleansing - $real->operational_spending, 2);
            $surplusData['net'][] = round($real->income_after_cleansing - $real->spending_after_cleansing, 2);
        }

        return [
            'years' => $years,
            'pad' => [
                'values' => $padData,
                'growth' => $padGrowth
            ],
            'autonomy' => $autonomyData,
            'spending' => $spendingData,
            'surplus' => $surplusData
        ];
    }
}
