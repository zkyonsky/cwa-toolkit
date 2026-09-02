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

        $financings = $gov->financing()->get();
        $financingSMI = $financings->firstWhere('lender', 'SMI');
        $financingLainnya = $financings->where('lender', '!=', 'SMI')->first();

        $financing = [
            'ds_exist' => $financingSMI->ds_exist ?? ($financings->first()->ds_exist ?? 0),
            'os_debt_smi' => $financingSMI->os_debt ?? 0,
            'os_debt_lainnya' => $financingLainnya->os_debt ?? 0,
        ];

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
            'ds_exist' => 'nullable|numeric',
            'outstanding_smi' => 'nullable|numeric',
            'outstanding_lainnya' => 'nullable|numeric',
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

        if ($request->has('ds_exist') || $request->has('outstanding_smi')) {
            $gov->financing()->updateOrCreate(
                ['gov_code' => $gov->code, 'lender' => 'SMI'],
                [
                    'ds_exist' => $request->ds_exist,
                    'os_debt' => $request->outstanding_smi
                ]
            );
        }

        if ($request->has('outstanding_lainnya')) {
            $gov->financing()->updateOrCreate(
                ['gov_code' => $gov->code, 'lender' => 'Lainnya'],
                ['os_debt' => $request->outstanding_lainnya]
            );
        }

        return redirect()->route("assessment-details.budget-real.edit", $assessment->id)->with("message", "DSCR Summary berhasil diisi!");
    }

    public function destroyBudgetPlan(Request $request, Assessment $assessment)
    {
        $request->validate([
            'year' => 'required|integer',
        ]);

        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;

        if (!$gov) {
            return redirect()->back()->with('error', 'Gov not found');
        }

        $gov->budget_plan()->where('year', $request->year)->delete();

        return redirect()->back()->with('message', "Data Budget Plan tahun {$request->year} berhasil dihapus.");
    }
}
