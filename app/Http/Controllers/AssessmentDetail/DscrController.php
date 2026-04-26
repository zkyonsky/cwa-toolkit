<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Budget_plan;
use App\Models\Financing;
use App\Models\Debt_service;

class DscrController extends Controller
{
    public function edit(Assessment $assessment)
    {
        $assessment->load(['assessee.gov', 'debtService']);
        $gov = $assessment->assessee->gov;
        if (!$gov)
            return redirect()->back()->with('error', 'Gov not found');

        $govName = $gov->name;
        $year = $gov->budget_plan()->max('year') ?? date('Y');

        $budgetPlan = $gov->budget_plan()->where('year', $year)->first()
            ?? new Budget_plan(['year' => $year, 'gov_code' => $gov->code]);

        $financing = $gov->financing()->first() ?? new Financing(['gov_code' => $gov->code]);

        $debtService = $assessment->debtService ?: new Debt_service([
            'assessment_id' => $assessment->id,
            'advantage' => '',
            'challenge' => '',
        ]);

        return Inertia::render('assessmentDetail/EditDscr', [
            'assessment' => $assessment,
            'govName' => $govName,
            'year' => $year,
            'budgetPlan' => $budgetPlan,
            'financing' => $financing,
            'debtService' => $debtService,
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        \Log::info('DSCR update request', $request->all());

        $request->validate([
            'advantage' => 'nullable|string',
            'challenge' => 'nullable|string',
            'dscr' => 'nullable|numeric',
            'limit' => 'nullable|numeric',
            'max_loan' => 'nullable|numeric',
            'loan_withdrawal_plus_os' => 'nullable|numeric',
            'unappropiated_revenue' => 'nullable|numeric',
            'budgetPlan' => 'nullable|array',
        ]);

        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;

        if ($request->has('budgetPlan') && $gov) {
            $bpData = $request->input('budgetPlan');
            if (isset($bpData['year'])) {
                $gov->budget_plan()->updateOrCreate(['year' => $bpData['year']], $bpData);

                if (!$request->has('advantage')) {
                    return redirect()->back()->with('message', 'Budget Plan data added successfully!');
                }
            }
        }

        Debt_service::updateOrCreate(
            ['assessment_id' => $assessment->id],
            [
                'advantage' => $request->advantage,
                'challenge' => $request->challenge,
                'dscr' => $request->dscr,
                'limit' => $request->limit,
                'max_loan' => $request->max_loan,
                'loan_withdrawal_plus_os' => $request->loan_withdrawal_plus_os,
                'unappropiated_revenue' => $request->unappropiated_revenue,
            ]
        );

        return redirect()->route('assessments.index')->with('message', 'DSCR Summary updated successfully!');
    }
}
