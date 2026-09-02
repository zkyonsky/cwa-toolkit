<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\infras_service;
use App\Models\infras_priority;
use App\Models\infras_conclusion;

class InfrastructureController extends Controller
{
    public function edit(Assessment $assessment)
    {
        $assessment->load(['infrasService', 'infrasPriorities', 'infrasConclusion', 'assessee.gov']);
        $gov = $assessment->assessee->gov;
        if (!$gov)
            return redirect()->back()->with('error', 'Gov not found');

        $assessmentYear = date('Y', strtotime($assessment->date));

        $latestYear = $gov->economy_indicator()->max('year') ?? ($assessmentYear - 1);
        $year1 = (int) $latestYear;
        $year2 = $year1 - 1;

        $indicators = $gov->economy_indicator()
            ->whereIn('year', [$year1, $year2])
            ->get()
            ->keyBy('year');

        $infrasService = $assessment->infrasService ?: new infras_service([
            'education' => null,
            'health' => null,
            'water' => null,
            'waste' => null,
            'it' => null,
            'agriculture' => null,
            'transport' => null,
            'electricity' => null,
            'sport_art_culture' => null,
            'tourism' => null,
            'food' => null,
            'commerce' => null,
            'road' => null,
        ]);

        $infrasPriorities = $assessment->infrasPriorities;
        $infrasConclusion = $assessment->infrasConclusion ?: new infras_conclusion([
            'advantage' => '',
            'challenge' => ''
        ]);

        return Inertia::render('assessmentDetail/EditInfrastructure', [
            'assessment' => $assessment,
            'infrasService' => $infrasService,
            'infrasPriorities' => $infrasPriorities,
            'infrasConclusion' => $infrasConclusion,
            'indicators' => $indicators,
            'year1' => $year1,
            'year2' => $year2
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $request->validate([
            'services'   => 'required|array',
            'priorities' => 'present|array',
            'conclusion' => 'required|array',
            'indicators' => 'nullable|array',
        ]);

        // 1. Update Services
        $assessment->infrasService()->updateOrCreate(
            ['assessment_id' => $assessment->id],
            $request->input('services')
        );

        // 2. Sync Priorities
        $assessment->infrasPriorities()->delete();
        $prioritiesData = [];
        foreach ($request->input('priorities') as $priority) {
            $prioritiesData[] = [
                'assessment_id' => $assessment->id,
                'plan' => $priority['plan'] ?? '',
                'exp_outcome' => $priority['exp_outcome'] ?? '',
                'rank' => $priority['rank'] ?? 0,
                'estimated_cost' => $priority['estimated_cost'] ?? 0,
                'fund_source' => $priority['fund_source'] ?? '',
                'alt_fund_need' => $priority['alt_fund_need'] ?? 0,
                'alt_fund_source' => $priority['alt_fund_source'] ?? '',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($prioritiesData)) {
            infras_priority::insert($prioritiesData);
        }

        // 3. Update Conclusion
        $conclusionData = $request->input('conclusion');
        $assessment->infrasConclusion()->updateOrCreate(
            ['assessment_id' => $assessment->id],
            [
                'advantage' => $conclusionData['advantage'] ?? '',
                'challenge' => $conclusionData['challenge'] ?? ''
            ]
        );

        // 4. Update Indicators
        if ($request->has('indicators')) {
            $assessment->load('assessee.gov');
            $gov = $assessment->assessee->gov;
            foreach ($request->input('indicators') as $year => $data) {
                $gov->economy_indicator()->updateOrCreate(
                    ['year' => $year],
                    [
                        'infras_real' => $data['infras_real'] ?? 0,
                        'fiscal_ratio' => $data['fiscal_ratio'] ?? 0,
                    ]
                );
            }
        }

        return redirect()->route("assessment-details.dscr.edit", $assessment->id)->with("message", "Infrastruktur berhasil disimpan!");
    }
}
