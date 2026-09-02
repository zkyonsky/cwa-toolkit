<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Economy_indicator;

class EconomyConditionController extends Controller
{
    public function edit(Assessment $assessment)
    {
        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;
        if (!$gov)
            return redirect()->back()->with('error', 'Gov not found');

        $govName = $gov->name;
        $govLevel = $gov->level;
        $assessmentYear = date('Y', strtotime($assessment->date));

        $latestEconomy = $gov->economy_indicator()->max('year');
        $latestSectoral = $gov->sectoral_gdp()->max('year');
        $year = (int) (max($latestEconomy, $latestSectoral) ?? ($assessmentYear - 1));

        $economyIndicator = $gov->economy_indicator()->firstOrCreate(
            ['year' => $year],
            [
                'poverty' => 0,
                'unemployment' => 0,
                'hdci' => 0,
                'gdp_perkapita' => 0,
                'gdp_growth' => 0,
                'gdp' => 0,
            ]
        );

        $sectoralGdp = $gov->sectoral_gdp()->firstOrCreate(
            ['year' => $year],
            [
                'agriculture_forestry_fishery' => 0,
                'mining_quarrying' => 0,
                'processing_industry' => 0,
                'electricity_gas' => 0,
                'water_waste' => 0,
                'contruction' => 0,
                'trade_vehicle_repair' => 0,
                'transportation_warehousing' => 0,
                'acomodation_food_beverage' => 0,
                'information_communication' => 0,
                'finance_insurance' => 0,
                'real_estate' => 0,
                'company_service' => 0,
                'gov_adm_defense_sosial_security' => 0,
                'education_service' => 0,
                'health_social_service' => 0,
                'other_service' => 0,
            ]
        );

        $compareIndicators = Economy_indicator::where('year', $year)
            ->whereHas('gov', function ($query) use ($govLevel) {
                $query->where('level', $govLevel);
            })
            ->get();

        $comparison = [
            'poverty' => [
                'min' => $compareIndicators->min('poverty') ?? 0,
                'avg' => $compareIndicators->avg('poverty') ?? 0,
                'max' => $compareIndicators->max('poverty') ?? 0,
            ],
            'unemployment' => [
                'min' => $compareIndicators->min('unemployment') ?? 0,
                'avg' => $compareIndicators->avg('unemployment') ?? 0,
                'max' => $compareIndicators->max('unemployment') ?? 0,
            ],
            'hdci' => [
                'min' => $compareIndicators->min('hdci') ?? 0,
                'avg' => $compareIndicators->avg('hdci') ?? 0,
                'max' => $compareIndicators->max('hdci') ?? 0,
            ],
            'gdp_perkapita' => [
                'min' => $compareIndicators->min('gdp_perkapita') ?? 0,
                'avg' => $compareIndicators->avg('gdp_perkapita') ?? 0,
                'max' => $compareIndicators->max('gdp_perkapita') ?? 0,
            ],
            'gdp_growth' => [
                'min' => $compareIndicators->min('gdp_growth') ?? 0,
                'avg' => $compareIndicators->avg('gdp_growth') ?? 0,
                'max' => $compareIndicators->max('gdp_growth') ?? 0,
            ]
        ];

        return Inertia::render('assessmentDetail/EditEconomyCondition', [
            'assessment' => $assessment,
            'govName' => $govName,
            'govLevel' => $govLevel,
            'year' => $year,
            'economyIndicator' => $economyIndicator,
            'sectoralGdp' => $sectoralGdp,
            'comparison' => $comparison,
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;

        $request->validate([
            'year' => 'nullable|integer',
            'economyIndicator' => 'required|array',
            'sectoralGdp' => 'required|array',
        ]);

        $year = (int) ($request->input('year') ?? (date('Y', strtotime($assessment->date)) - 1));

        $sectoralGdpData = $request->input('sectoralGdp');
        $sectors = [
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
            'other_service'
        ];

        $totalGdp = 0;
        foreach ($sectors as $sector) {
            $totalGdp += (float) ($sectoralGdpData[$sector] ?? 0);
        }

        $economyIndicatorData = $request->input('economyIndicator');
        $economyIndicatorData['gdp'] = $totalGdp;

        unset($economyIndicatorData['id'], $economyIndicatorData['gov_code'], $economyIndicatorData['created_at'], $economyIndicatorData['updated_at']);
        unset($sectoralGdpData['id'], $sectoralGdpData['gov_code'], $sectoralGdpData['created_at'], $sectoralGdpData['updated_at']);

        $gov->economy_indicator()->updateOrCreate(['year' => $year], $economyIndicatorData);
        $gov->sectoral_gdp()->updateOrCreate(['year' => $year], $sectoralGdpData);

        return redirect()->route("assessment-details.indicative-rating.edit", $assessment->id)->with("message", "Ekonomi berhasil diisi!");
    }

    public function destroy(Request $request, Assessment $assessment)
    {
        $request->validate([
            'year' => 'required|integer',
        ]);

        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;

        if (!$gov) {
            return redirect()->back()->with('error', 'Gov not found');
        }

        $year = $request->year;

        $gov->economy_indicator()->where('year', $year)->delete();
        $gov->sectoral_gdp()->where('year', $year)->delete();

        return redirect()->back()->with('message', "Data ekonomi tahun {$year} berhasil dihapus.");
    }
}
