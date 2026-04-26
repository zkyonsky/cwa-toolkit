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
        $assessment->load(['actionPlans', 'assessee.gov']);

        $challenges = Challenge::with(['challengeActions', 'category'])->get();

        return Inertia::render('assessmentDetail/EditActionPlan', [
            'assessment' => $assessment,
            'actionPlans' => $assessment->actionPlans,
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

        return redirect()->back()->with('message', 'Action Plan updated successfully!');
    }
}
