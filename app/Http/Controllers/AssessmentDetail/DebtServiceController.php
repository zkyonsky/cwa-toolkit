<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Debt_service;

class DebtServiceController extends Controller
{
    public function edit(Assessment $assessment)
    {
        $assessment->load(['assessee.gov', 'debtService']);
        $gov = $assessment->assessee->gov;
        if (!$gov)
            return redirect()->back()->with('error', 'Gov not found');

        $govName = $gov->name;

        $financingSmi = $gov->financing()->where('lender', 'SMI')->first();
        $avgExisPayment = $financingSmi ? $financingSmi->ds_exist : 0;

        $debtService = $assessment->debtService ?: new Debt_service([
            'assessment_id' => $assessment->id,
            'plafond' => 0,
            'tenor' => 0,
            'disbursement_period' => 0,
            'first_disbursement' => date('Y-m-d'),
            'interest' => 0,
            'dscr' => 0,
            'limit' => 0,
            'avg_annual_return' => 0,
            'avg_annual_interest' => 0,
            'avg_annual_cost' => 0,
            'avg_exis_payment' => $avgExisPayment,
        ]);

        if (!$assessment->debtService) {
            $debtService->avg_exis_payment = $avgExisPayment;
        } else {
            $assessment->debtService->avg_exis_payment = $avgExisPayment;
            $debtService = $assessment->debtService;
        }

        return Inertia::render('assessmentDetail/EditDebtService', [
            'assessment' => $assessment,
            'govName' => $govName,
            'debtService' => $debtService
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $request->validate([
            'plafond' => 'required|numeric',
            'tenor' => 'required|numeric',
            'disbursement_period' => 'required|numeric',
            'first_disbursement' => 'required|date',
            'interest' => 'required|numeric',
        ]);

        $assessment->load('assessee.gov');
        $gov = $assessment->assessee->gov;
        $financingSmi = $gov->financing()->where('lender', 'SMI')->first();
        $avgExisPayment = $financingSmi ? $financingSmi->ds_exist : 0;

        $amortizationService = new \App\Services\AmortizationService();
        $calc = $amortizationService->hitungPinjamanProgresif(
            $request->plafond,
            $request->interest,
            $request->tenor * 12,
            $request->disbursement_period,
            $request->first_disbursement
        );

        Debt_service::updateOrCreate(
            ['assessment_id' => $assessment->id],
            [
                'plafond' => $request->plafond,
                'tenor' => $request->tenor,
                'disbursement_period' => $request->disbursement_period,
                'first_disbursement' => $request->first_disbursement,
                'interest' => $request->interest,
                'avg_annual_return' => $calc['hasil_cicilan']['rata_pokok_tahunan'] ?? 0,
                'avg_annual_interest' => $calc['hasil_cicilan']['rata_bunga_tahunan'] ?? 0,
                'avg_annual_cost' => $calc['parameter']['biaya_provisi'] ?? 0,
                'avg_exis_payment' => $avgExisPayment,
            ]
        );

        return redirect()->back()->with('message', 'Debt Service updated successfully!');
    }
}
