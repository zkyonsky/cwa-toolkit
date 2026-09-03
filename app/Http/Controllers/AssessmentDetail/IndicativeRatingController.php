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

        // Perhitungan: Skor Kategori PDRB Per Kapita
        // Tujuan: Menentukan rasio kategori PDRB per kapita sebagai bagian dari komponen ekonomi.
        // Sumber Parameter: Field gdp_perkapita dari model Economy_indicator.
        // Diproses di: IndicativeRatingService->hitungKategoriPDRB() lalu masuk ke QuantitativeRatingService->calculate()
        $gdpPerkapita = $economyIndicator->gdp_perkapita ?? 0;
        $skorPerkapita = $ratingService->hitungKategoriPDRB($gdpPerkapita)['rasio'];
        // Perhitungan: Kategori Konsentrasi PDRB
        // Tujuan: Mengkategorikan total PDRB daerah menjadi Tinggi, Sedang, atau Rendah untuk penyesuaian skor PDRB.
        // Sumber Parameter: $totalGdp dari field gdp model Economy_indicator.
        // Diproses di: IndicativeRatingService->hitungKonsentrasiPDRB() lalu masuk ke QuantitativeRatingService->calculate()
        $katKonsentrasi = $ratingService->hitungKonsentrasiPDRB($totalGdp);
        // Perhitungan: Tingkat Pengangguran Terbuka
        // Tujuan: Menentukan rasio berdasarkan tingkat pengangguran sebagai indikator kesehatan ekonomi.
        // Sumber Parameter: Field unemployment dari model Economy_indicator.
        // Diproses di: IndicativeRatingService->hitungKategoriTingkatPengangguran() di dalam QuantitativeRatingService->calculate()
        $pengangguran = $economyIndicator->unemployment ?? 0;
        // Perhitungan: Indeks Pembangunan Manusia (IPM)
        // Tujuan: Menentukan rasio pembangunan manusia sebagai indikator ekonomi/kualitas daerah.
        // Sumber Parameter: Field hdci dari model Economy_indicator.
        // Diproses di: IndicativeRatingService->hitungKategoriIPM() di dalam QuantitativeRatingService->calculate()
        $ipm = $economyIndicator->hdci ?? 0;

        // Perhitungan: Rasio PAD terhadap Total Pendapatan
        // Tujuan: Menentukan skor kemandirian anggaran Pemda berdasarkan porsi PAD.
        // Sumber Parameter: Field pad_revenue dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungPadPendapatan() lalu masuk ke QuantitativeRatingService->calculate()
        $padRevenue = $financialIndicator->pad_revenue ?? 0;
        $skorPadPendapatan = $ratingService->hitungPadPendapatan($padRevenue)['rasio'];
        // Perhitungan: Volatilitas PAD
        // Tujuan: Menentukan kategori volatilitas (Volatil / Tidak Volatil) sebagai penyesuaian skor kemandirian anggaran.
        // Sumber Parameter: Field volatil_pad dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungVolatilPad() lalu masuk ke QuantitativeRatingService->calculate()
        $volatilPad = $financialIndicator->volatil_pad ?? 0;
        $katVolatilPad = $ratingService->hitungVolatilPad($volatilPad);
        // Perhitungan: Saldo Operasi terhadap Total Pendapatan
        // Tujuan: Menilai sejauh mana penghasilan menutupi belanja operasional.
        // Sumber Parameter: Field operation_revenue dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungOperasiPendapatan() di dalam QuantitativeRatingService->calculate()
        $operasiPendapatan = $financialIndicator->operation_revenue ?? 0;

        // Perhitungan: Belanja Modal terhadap Total Belanja
        // Tujuan: Menilai porsi belanja modal untuk mengukur efektifitas belanja daerah.
        // Sumber Parameter: Field capital_spending dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungBelanjaModal() lalu masuk ke QuantitativeRatingService->calculate()
        $capSpending = $financialIndicator->capital_spending ?? 0;
        $skorModalBelanja = $ratingService->hitungBelanjaModal($capSpending)['rasio'];
        // Perhitungan: Belanja Pegawai terhadap Total Belanja
        // Tujuan: Menilai porsi belanja pegawai untuk mengukur efektifitas belanja.
        // Sumber Parameter: Field employee_spending dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungPegawaiBelanja() lalu masuk ke QuantitativeRatingService->calculate()
        $empSpending = $financialIndicator->employee_spending ?? 0;
        $skorPegawaiBelanja = $ratingService->hitungPegawaiBelanja($empSpending)['rasio'];

        // Perhitungan: Pertumbuhan PAD 3 Tahun Terakhir
        // Tujuan: Mengukur konsistensi dan kualitas penyusunan anggaran dari pertumbuhan PAD historis.
        // Sumber Parameter: Field pad_last_three_year dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungPadTigaTahun() di dalam QuantitativeRatingService->calculate()
        $padTigaTahun = $financialIndicator->pad_last_three_year ?? 0;
        // Perhitungan: Rasio Utang terhadap Total Pendapatan
        // Tujuan: Menilai beban utang daerah dibandingkan dengan pendapatannya.
        // Sumber Parameter: Field debt_revenue dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungUtangPendapatan() lalu masuk ke QuantitativeRatingService->calculate()
        $debtRevenue = $financialIndicator->debt_revenue ?? 0;
        $skorUtangPendapatan = $ratingService->hitungUtangPendapatan($debtRevenue)['rasio'];
        // Perhitungan: Rasio Utang terhadap PDRB
        // Tujuan: Menilai beban utang daerah dibandingkan dengan total output ekonomi daerah (PDRB).
        // Sumber Parameter: Field debt_gdp dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungUtangPdrb() lalu masuk ke QuantitativeRatingService->calculate()
        $debtGdp = $financialIndicator->debt_gdp ?? 0;
        $skorUtangPdrb = $ratingService->hitungUtangPdrb($debtGdp)['rasio'];

        // Perhitungan: Debt Service Coverage Ratio (DSCR)
        // Tujuan: Mengukur kapasitas daerah (likuiditas) dalam membayar kewajiban utang.
        // Sumber Parameter: Field dscr dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungDscr() lalu masuk ke QuantitativeRatingService->calculate()
        $dscr = $financialIndicator->dscr ?? 0;
        $skorDscr = $ratingService->hitungDscr($dscr)['rasio'];
        // Perhitungan: Debt Service terhadap Total Pendapatan
        // Tujuan: Mengukur seberapa besar porsi pendapatan yang digunakan untuk membayar utang.
        // Sumber Parameter: Field ds_revenue dari model Financial_indicator.
        // Diproses di: IndicativeRatingService->hitungDsPendapatan() lalu masuk ke QuantitativeRatingService->calculate()
        $dsRevenue = $financialIndicator->ds_revenue ?? 0;
        $skorDsPendapatan = $ratingService->hitungDsPendapatan($dsRevenue)['rasio'];

        // Perhitungan: Kapasitas Fiskal
        // Tujuan: Menentukan kategori fiskal daerah untuk menyesuaikan skor rating.
        // Sumber Parameter: Field fiscal_capacity dari model Financial_indicator.
        // Diproses di: QuantitativeRatingService->calculate() -> kapasitasFiskal()
        $katKapasitasFiskal = !empty($financialIndicator->fiscal_capacity) ? $financialIndicator->fiscal_capacity : "Sangat Rendah";

        // Perhitungan: Kualitas Pencatatan Keuangan (Opini BPK)
        // Tujuan: Mengevaluasi kualitas pencatatan dari riwayat opini WTP dan WDP dari BPK 3 tahun terakhir.
        // Sumber Parameter: Relasi budget_real dari model Gov (field bpk_opinion).
        // Diproses di: QuantitativeRatingService->calculate() -> kualitas_pencatatan_keuangan()
        $years = $gov->budget_real()->latest()->limit(3)->get()->pluck('year')->toArray();
        $budgetReals = $gov->budget_real()->whereIn('year', $years)->get();
        $wtpCount = $budgetReals->where('bpk_opinion', 'WTP')->count();
        $wdpCount = $budgetReals->where('bpk_opinion', 'WDP')->count();
        $syaratMinimum = ($wdpCount + $wtpCount >= 3) ? 'Memenuhi syarat minimum' : 'Tidak memenuhi syarat minimum';

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
