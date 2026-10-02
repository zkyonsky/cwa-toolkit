<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Challenge;

class ActionPlanController extends Controller
{
    public function edit(Assessment $assessment)
    {
        $assessment->load(['actionPlans', 'assessee.gov', 'infrasConclusion', 'debtService', 'selfAssessment']);

        $actionPlans = $assessment->actionPlans;

        if ($actionPlans->isEmpty()) {
            $defaultAspects = [
                'Kondisi Infrastruktur Existing',
                'Kondisi Kemampuan Meminjam (DSCR)',
                'Kondisi Keuangan/Fiskal',
                'Kondisi Ekonomi',
                'Kondisi Politik',
            ];

            $defaults = collect();
            foreach ($defaultAspects as $aspect) {
                $defaults->push([
                    'conclusion' => $aspect,
                    'challenge' => '',
                    'action_plan' => '',
                ]);
            }

            $actionPlans = $defaults;
        }

        $challenges = Challenge::with(['challengeActions', 'category'])->get();

        return Inertia::render('assessmentDetail/EditActionPlan', [
            'assessment' => $assessment,
            'actionPlans' => $actionPlans,
            'challenges' => $challenges
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $request->validate([
            'actionPlans' => 'required|array',
        ]);

        $assessment->actionPlans()->delete();

        foreach ($request->actionPlans as $plan) {
            $assessment->actionPlans()->create([
                'conclusion' => $plan['conclusion'] ?? '',
                'challenge' => $plan['challenge'] ?? '',
                'action_plan' => $plan['action_plan'] ?? '',
            ]);
        }

        return redirect()->route("assessment-details.report.show", $assessment->id)->with("message", "Form Assessment berhasil diselesaikan, silakan lihat Laporan!");
    }
}
