<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use Inertia\Inertia;

use App\Models\Budget_real;
use App\Models\Economy_indicator;
use App\Models\Sectoral_gdp;
use App\Models\Gov;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function show(Assessment $assessment)
    {
        $assessment->load([
            'assessee.gov',
            'infrasConclusion',
            'debtService',
            'selfAssessment',
            'actionPlans'
        ]);

        $gov = $assessment->assessee->gov;
        $economyIndicator = null;
        $chartData = null;
        $economyComparison = null;
        $topSectors = [];
        $budgetRealsData = [];
        
        if ($gov) {
            $assessmentYear = date('Y', strtotime($assessment->date));
            $latestEconomy = $gov->economy_indicator()->max('year');
            $latestSectoral = $gov->sectoral_gdp()->max('year');
            $year = (int) (max($latestEconomy, $latestSectoral) ?? ($assessmentYear - 1));
            
            $economyIndicator = $gov->economy_indicator()->where('year', $year)->first();

            // Fetch latest 3-year Budget Real data available for the gov
            $budgetReals = Budget_real::where('gov_code', $gov->code)
                ->orderBy('year', 'desc')
                ->take(3)
                ->get()
                ->sortBy('year')
                ->values();
            $budgetRealsData = $budgetReals;
            $chartData = $this->processChartData($budgetReals);

            // Fetch economy comparison
            $govLevel = $gov->level;
            $govCodesSameLevel = Gov::where('level', $govLevel)->pluck('code');
            
            $aggregateQuery = Economy_indicator::where('year', $year)
                ->whereIn('gov_code', $govCodesSameLevel)
                ->selectRaw('
                    MIN(poverty) as min_poverty, AVG(poverty) as avg_poverty, MAX(poverty) as max_poverty,
                    MIN(unemployment) as min_unemployment, AVG(unemployment) as avg_unemployment, MAX(unemployment) as max_unemployment,
                    MIN(hdci) as min_hdci, AVG(hdci) as avg_hdci, MAX(hdci) as max_hdci,
                    MIN(gdp_perkapita) as min_gdp_perkapita, AVG(gdp_perkapita) as avg_gdp_perkapita, MAX(gdp_perkapita) as max_gdp_perkapita,
                    MIN(gdp_growth) as min_gdp_growth, AVG(gdp_growth) as avg_gdp_growth, MAX(gdp_growth) as max_gdp_growth
                ')
                ->first();

            $economyComparison = $aggregateQuery;

            // Fetch top 3 priority sectors
            $sectoralGdp = Sectoral_gdp::where('gov_code', $gov->code)
                ->where('year', $year)
                ->first();

            if ($sectoralGdp) {
                $sectors = [
                    '1. Pertanian, Kehutanan, dan Perikanan' => $sectoralGdp->agriculture_forestry_fishery,
                    '2. Pertambangan dan Penggalian' => $sectoralGdp->mining_quarrying,
                    '3. Industri Pengolahan' => $sectoralGdp->processing_industry,
                    '4. Pengadaan Listrik dan Gas' => $sectoralGdp->electricity_gas,
                    '5. Pengadaan Air, Pengelolaan Sampah, Limbah dan Daur Ulang' => $sectoralGdp->water_waste,
                    '6. Konstruksi' => $sectoralGdp->contruction,
                    '7. Perdagangan Besar dan Eceran; Reparasi Mobil dan Sepeda Motor' => $sectoralGdp->trade_vehicle_repair,
                    '8. Transportasi dan Pergudangan' => $sectoralGdp->transportation_warehousing,
                    '9. Penyediaan Akomodasi dan Makan Minum' => $sectoralGdp->acomodation_food_beverage,
                    '10. Informasi dan Komunikasi' => $sectoralGdp->information_communication,
                    '11. Jasa Keuangan dan Asuransi' => $sectoralGdp->finance_insurance,
                    '12. Real Estate' => $sectoralGdp->real_estate,
                    '13. Jasa Perusahaan' => $sectoralGdp->company_service,
                    '14. Administrasi Pemerintahan, Pertahanan dan Jaminan Sosial Wajib' => $sectoralGdp->gov_adm_defense_sosial_security,
                    '15. Jasa Pendidikan' => $sectoralGdp->education_service,
                    '16. Jasa Kesehatan dan Kegiatan Sosial' => $sectoralGdp->health_social_service,
                    '17. Jasa lainnya' => $sectoralGdp->other_service,
                ];

                $totalGdp = array_sum($sectors);

                arsort($sectors);

                $topSectors = [];
                $count = 1;
                foreach ($sectors as $name => $value) {
                    if ($count > 3) break;
                    $topSectors[] = [
                        'rank' => $count,
                        'name' => $name,
                        'value' => $value,
                        'percentage' => $totalGdp > 0 ? ($value / $totalGdp) * 100 : 0
                    ];
                    $count++;
                }
            }
        }

        $financialIndicator = \App\Models\Financial_indicator::where('assessment_id', $assessment->id)->first();

        return Inertia::render('assessmentDetail/Report', [
            'assessment' => $assessment,
            'infrasConclusion' => $assessment->infrasConclusion,
            'economyIndicator' => $economyIndicator,
            'financialIndicator' => $financialIndicator,
            'debtService' => $assessment->debtService,
            'actionPlans' => $assessment->actionPlans,
            'chartData' => $chartData,
            'budgetReals' => $budgetRealsData,
            'economyComparison' => $economyComparison,
            'topSectors' => $topSectors,
            'economyYear' => $year ?? null
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

            // Surplus/Deficit (as % of income)
            $surplusData['operating'][] = round((($real->income_after_cleansing - $real->operational_spending) / $totalIncome) * 100, 2);
            $surplusData['net'][] = round((($real->income_after_cleansing - $real->spending_after_cleansing) / $totalIncome) * 100, 2);
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
