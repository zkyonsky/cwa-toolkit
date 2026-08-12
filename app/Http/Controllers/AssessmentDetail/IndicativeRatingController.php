<?php

namespace App\Http\Controllers\AssessmentDetail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Assessment;
use App\Models\Financial_indicator;
use App\Models\Self_assessment;

class IndicativeRatingController extends Controller
{
    public function edit(Assessment $assessment)
    {
        $assessment->load(['assessee.gov', 'selfAssessment', 'debtService']);
        $gov = $assessment->assessee->gov;
        if (!$gov)
            return redirect()->back()->with('error', 'Gov not found');

        $govLevel = $gov->level ?? '';
        $assessmentYear = date('Y', strtotime($assessment->date));

        $latestEconomy = $gov->economy_indicator()->max('year');
        $latestSectoral = $gov->sectoral_gdp()->max('year');
        $year = (int) (max($latestEconomy, $latestSectoral) ?? ($assessmentYear - 1));

        $economyIndicator = $gov->economy_indicator()->where('year', $year)->first();
        $financialIndicator = Financial_indicator::where('assessment_id', $assessment->id)->first();

        $totalGdp = $economyIndicator->gdp ?? 0;

        $ratingService = new \App\Services\IndicativeRatingService();
        $quantService = new \App\Services\QuantitativeRatingService();

        $gdpPerkapita = $economyIndicator->gdp_perkapita ?? 0;
        $skorPerkapita = $ratingService->hitungKategoriPDRB($gdpPerkapita)['rasio'];
        $katKonsentrasi = $ratingService->hitungKonsentrasiPDRB($totalGdp);
        $pengangguran = $economyIndicator->unemployment ?? 0;
        $ipm = $economyIndicator->hdci ?? 0;

        $padRevenue = $financialIndicator->pad_revenue ?? 0;
        $skorPadPendapatan = $ratingService->hitungPadPendapatan($padRevenue)['rasio'];
        $volatilPad = $financialIndicator->volatil_pad ?? 0;
        $katVolatilPad = $ratingService->hitungVolatilPad($volatilPad);
        $operasiPendapatan = $financialIndicator->operation_revenue ?? 0;

        $capSpending = $financialIndicator->capital_spending ?? 0;
        $skorModalBelanja = $ratingService->hitungBelanjaModal($capSpending)['rasio'];
        $empSpending = $financialIndicator->employee_spending ?? 0;
        $skorPegawaiBelanja = $ratingService->hitungPegawaiBelanja($empSpending)['rasio'];

        $padTigaTahun = $financialIndicator->pad_last_three_year ?? 0;
        $debtRevenue = $financialIndicator->debt_revenue ?? 0;
        $skorUtangPendapatan = $ratingService->hitungUtangPendapatan($debtRevenue)['rasio'];
        $debtGdp = $financialIndicator->debt_gdp ?? 0;
        $skorUtangPdrb = $ratingService->hitungUtangPdrb($debtGdp)['rasio'];

        $dscr = $financialIndicator->dscr ?? 0;
        $skorDscr = $ratingService->hitungDscr($dscr)['rasio'];
        $dsRevenue = $financialIndicator->ds_revenue ?? 0;
        $skorDsPendapatan = $ratingService->hitungDsPendapatan($dsRevenue)['rasio'];

        $katKapasitasFiskal = !empty($financialIndicator->fiscal_capacity) ? $financialIndicator->fiscal_capacity : "Sedang";

        $years = $gov->budget_real()->latest()->limit(3)->get()->pluck('year')->toArray();
        $budgetReals = $gov->budget_real()->whereIn('year', $years)->get();
        $wtpCount = $budgetReals->where('bpk_opinion', 'WTP')->count();
        $wdpCount = $budgetReals->where('bpk_opinion', 'WDP')->count();
        $syaratMinimum = ($wdpCount >= 3 || $wtpCount >= 3) ? 'Memenuhi syarat minimum' : 'Tidak memenuhi syarat minimum';

        $ratingResult = $quantService->calculate(
            $skorPerkapita,
            $katKonsentrasi,
            $pengangguran,
            $ipm,
            $skorPadPendapatan,
            $katVolatilPad,
            $operasiPendapatan,
            $skorModalBelanja,
            $skorPegawaiBelanja,
            $padTigaTahun,
            $skorUtangPendapatan,
            $skorUtangPdrb,
            $skorDscr,
            $skorDsPendapatan,
            $katKapasitasFiskal,
            $govLevel,
            $syaratMinimum,
            $wtpCount
        );

        $ratingLabels = [
            1 => 'Sangat Memadai',
            2 => 'Sangat Memadai',
            3 => 'Memadai',
            4 => 'Memadai',
            5 => 'Tidak Memadai',
        ];
        $indicativeRating = $ratingLabels[$ratingResult['peringkat']] ?? 'Tidak Diketahui';

        if (is_null($assessment->economy_condition))
            $assessment->economy_condition = $ratingResult['skor_ekonomi'];
        if (is_null($assessment->financial_condition))
            $assessment->financial_condition = $ratingResult['skor_keuangan'];
        if (is_null($assessment->indicative_rating))
            $assessment->indicative_rating = $indicativeRating;

        $selfAssessment = $assessment->selfAssessment ?: new Self_assessment([
            'assessment_id' => $assessment->id,
            'social_politics_disturbance' => 'Tidak',
            'arrears_restructuring' => 'Tidak',
            'cashflow_availability' => 'Tidak',
        ]);

        return Inertia::render('assessmentDetail/EditIndicativeRating', [
            'assessment' => $assessment,
            'selfAssessment' => $selfAssessment,
            'ratingResult' => $ratingResult,
            'indicativeRating' => $indicativeRating,
        ]);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $request->validate([
            'economy_condition' => 'nullable|numeric',
            'financial_condition' => 'nullable|numeric',
            'indicative_rating' => 'nullable|string',
            'final_rating' => 'nullable|string',
            'selfAssessment' => 'required|array',
        ]);

        $assessment->update([
            'economy_condition' => $request->economy_condition,
            'financial_condition' => $request->financial_condition,
            'indicative_rating' => $request->indicative_rating,
            'final_rating' => $request->final_rating,
        ]);

        $assessment->selfAssessment()->updateOrCreate(
            ['assessment_id' => $assessment->id],
            $request->selfAssessment
        );

        return redirect()->route("assessment-details.action-plan.edit", $assessment->id)->with("message", "Rating berhasil diperbarui!");
    }
}
