<?php

namespace App\Services;

class IndicativeRatingService
{
    /**
     * Format output rasio dan kategori berdasarkan rasio standar atau terbalik (inverse).
     */
    private function formatOutput(int $rasio, bool $inverse = false): array
    {
        $kategori = [
            5 => $inverse ? 'Sangat Rendah' : 'Sangat Tinggi',
            4 => $inverse ? 'Rendah' : 'Tinggi',
            3 => 'Rata-rata/Sedang',
            2 => $inverse ? 'Tinggi' : 'Rendah',
            1 => $inverse ? 'Sangat Tinggi' : 'Sangat Rendah',
        ];

        return [
            'rasio' => $rasio,
            'kategori' => $kategori[$rasio] ?? $kategori[1]
        ];
    }

    /**
     * Fungsi untuk mencari Kategori PDRB HB per Kapita
     *
     * @param float $pdrb PDRB HB per Kapita dalam satuan Juta Rupiah (Berasal dari gdp_perkapita pada economy_indicator (input user pada halaman kondisi ekonomi)
     * @return array Kategori PDRB dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorPerkapita)
     */
    public function hitungKategoriPDRB(float $pdrb): array
    {
        if ($pdrb >= 91)
            $rasio = 5;
        elseif ($pdrb >= 51)
            $rasio = 4;
        elseif ($pdrb >= 38)
            $rasio = 3;
        elseif ($pdrb >= 28)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio);
    }

    /**
     * Fungsi untuk mencari Kategori Konsentrasi PDRB
     *
     * @param float $pdrb Total PDRB dalam satuan Juta Rupiah (Berasal dari Tingkat Konsentrasi PDRB (input user pada halaman kondisi ekonomi))
     * @return string Kategori Konsentrasi PDRB (Tinggi/Sedang/Rendah) yang akan masuk ke perhitungan skor kuantitatif (katKonsentrasi)
     */
    public function hitungKonsentrasiPDRB(float $pdrb): string
    {
        if ($pdrb >= 2500)
            return "Tinggi";
        if ($pdrb >= 1500)
            return "Sedang";
        return "Rendah";
    }

    /**
     * Fungsi untuk membandingkan PDRB dengan PDB Nasional
     * Fungsi ini belum digunakan pada perhitungan mana pun
     * @param float $pdrb Total PDRB dalam satuan Juta Rupiah
     * @param float $pdb_indonesia PDB Indonesia dalam satuan Juta Rupiah
     * @return string Kategori Perbandingan PDRB (Dibawah Nasional / Sama atau lebih tinggi)
     */
    public function hitungPerbandinganPDRB(float $pdrb, float $pdb_indonesia): string
    {
        return $pdrb < (1.1 * $pdb_indonesia) ? "Dibawah Nasional" : "Sama atau lebih tinggi dibanding Nasional";
    }

    /**
     * Fungsi untuk mencari Kategori Tingkat Pengangguran Terbuka
     *
     * @param float $pengangguran Persentase tingkat pengangguran (Berasal dari unemployment pada economy_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (pengangguran)
     */
    public function hitungKategoriTingkatPengangguran(float $pengangguran): array
    {
        if ($pengangguran < 2)
            $rasio = 5;
        elseif ($pengangguran < 4)
            $rasio = 4;
        elseif ($pengangguran < 6)
            $rasio = 3;
        elseif ($pengangguran < 8)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio, true);
    }

    /**
     * Fungsi untuk mencari Kategori Indeks Pembangunan Manusia (IPM)
     *
     * @param float $ipm Indeks Pembangunan Manusia dalam bentuk angka mutlak 0-100 (Berasal dari hdci pada economy_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (ipm)
     */
    public function hitungKategoriIPM(float $ipm): array
    {
        if ($ipm >= 79)
            $rasio = 5;
        elseif ($ipm >= 75)
            $rasio = 4;
        elseif ($ipm >= 72)
            $rasio = 3;
        elseif ($ipm >= 67)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio);
    }

    /**
     * Fungsi untuk mencari Kategori PAD terhadap Total Pendapatan
     *
     * @param float $pad_pendapatan Rasio PAD terhadap Total Pendapatan dalam bentuk persentase (Berasal dari pad_revenue pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorPadPendapatan)
     */
    public function hitungPadPendapatan(float $pad_pendapatan): array
    {
        $val = $pad_pendapatan;
        if ($val >= 24)
            $rasio = 5;
        elseif ($val >= 14)
            $rasio = 4;
        elseif ($val >= 9)
            $rasio = 3;
        elseif ($val >= 6)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio);
    }

    /**
     * Fungsi untuk mencari Volatilitas PAD
     *
     * @param float $volatil_pad Volatilitas PAD dalam bentuk persentase (Berasal dari volatil_pad pada financial_indicator)
     * @return string Kategori Volatilitas PAD (Volatil/Tidak Volatil) yang akan masuk ke perhitungan skor kuantitatif (katVolatilPad)
     */
    public function hitungVolatilPad(float $volatil_pad): string
    {
        return ($volatil_pad < 50) ? "Tidak Volatil" : "Volatil";
    }

    /**
     * Fungsi untuk mencari Kategori Saldo Operasi terhadap Total Pendapatan
     *
     * @param float $operasi_pendapatan Rasio Saldo Operasi terhadap Total Pendapatan dalam bentuk persentase (Berasal dari operation_revenue pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (operasiPendapatan)
     */
    public function hitungOperasiPendapatan(float $operasi_pendapatan): array
    {
        $val = $operasi_pendapatan;
        if ($val >= 43)
            $rasio = 5;
        elseif ($val >= 36)
            $rasio = 4;
        elseif ($val >= 29)
            $rasio = 3;
        else
            $rasio = 1;

        return $this->formatOutput($rasio);
    }

    /**
     * Fungsi untuk mencari Kategori Belanja Modal terhadap Total Belanja
     *
     * @param float $belanja_modal Rasio Belanja Modal terhadap Total Belanja dalam bentuk persentase (Berasal dari capital_spending pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorModalBelanja)
     */
    public function hitungBelanjaModal(float $belanja_modal): array
    {
        $val = $belanja_modal;
        if ($val >= 31)
            $rasio = 5;
        elseif ($val >= 24)
            $rasio = 4;
        elseif ($val >= 20)
            $rasio = 3;
        elseif ($val >= 16)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio);
    }

    /**
     * Fungsi untuk mencari Kategori Belanja Pegawai terhadap Total Belanja
     *
     * @param float $pegawai_belanja Rasio Belanja Pegawai terhadap Total Belanja dalam bentuk persentase (Berasal dari employee_spending pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorPegawaiBelanja)
     */
    public function hitungPegawaiBelanja(float $pegawai_belanja): array
    {
        $val = $pegawai_belanja;
        if ($val < 32)
            $rasio = 5;
        elseif ($val < 40)
            $rasio = 4;
        elseif ($val < 45)
            $rasio = 3;
        elseif ($val < 52)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio, true);
    }

    /**
     * Fungsi untuk mencari Kategori Pertumbuhan PAD 3 Tahun Terakhir
     *
     * @param float $pad_tiga_tahun Rata-rata pertumbuhan PAD 3 tahun dalam bentuk desimal (Berasal dari pad_last_three_year pada financial_indicator) - akan dikali 100 untuk menjadi persentase
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (padTigaTahun)
     */
    public function hitungPadTigaTahun(float $pad_tiga_tahun): array
    {
        $val = $pad_tiga_tahun * 100;
        if ($val >= 151)
            $rasio = 5;
        elseif ($val >= 111)
            $rasio = 4;
        elseif ($val >= 91)
            $rasio = 3;
        elseif ($val >= 51)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio);
    }

    /**
     * Fungsi untuk mencari Kategori Total Utang terhadap PDRB
     *
     * @param float $utang_pdrb Rasio Utang terhadap PDRB dalam bentuk persentase (Berasal dari debt_gdp pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorUtangPdrb)
     */
    public function hitungUtangPdrb(float $utang_pdrb): array
    {
        $val = $utang_pdrb;
        if ($val < 0.07)
            $rasio = 5;
        elseif ($val < 0.21)
            $rasio = 4;
        elseif ($val < 25)
            $rasio = 3;
        elseif ($val < 60)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio, true);
    }

    /**
     * Fungsi untuk mencari Kategori Total Utang terhadap Total Pendapatan
     *
     * @param float $utang_pendapatan Rasio Utang terhadap Pendapatan dalam bentuk persentase (Berasal dari debt_revenue pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorUtangPendapatan)
     */
    public function hitungUtangPendapatan(float $utang_pendapatan): array
    {
        $val = $utang_pendapatan;
        if ($val < 0.6)
            $rasio = 5;
        elseif ($val < 6)
            $rasio = 4;
        elseif ($val < 45)
            $rasio = 3;
        elseif ($val < 75)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio, true);
    }

    /**
     * Fungsi untuk mencari Kategori Debt Service terhadap Total Pendapatan
     *
     * @param float $ds_pendapatan Rasio Debt Service terhadap Total Pendapatan dalam bentuk persentase (Berasal dari ds_revenue pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorDsPendapatan)
     */
    public function hitungDsPendapatan(float $ds_pendapatan): array
    {
        $val = $ds_pendapatan;
        if ($val < 0.48)
            $rasio = 5;
        elseif ($val < 18.12)
            $rasio = 4;
        elseif ($val < 28)
            $rasio = 3;
        elseif ($val < 63)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio, true);
    }

    /**
     * Fungsi untuk mencari Kategori Debt Service Coverage Ratio (DSCR)
     *
     * @param float $dscr Nilai mutlak DSCR dalam bentuk desimal (Berasal dari dscr pada financial_indicator)
     * @return array Kategori dan Rasio yang akan masuk ke perhitungan skor kuantitatif (skorDscr)
     */
    public function hitungDscr(float $dscr): array
    {
        $val = $dscr;
        if ($val >= 12.1)
            $rasio = 5;
        elseif ($val >= 2.6)
            $rasio = 3;
        elseif ($val >= 1.1)
            $rasio = 2;
        else
            $rasio = 1;

        return $this->formatOutput($rasio);
    }

    /**
     * Fungsi untuk mencari Kategori Kapasitas Fiskal
     *
     * @param float $fiscal_ratio Indeks Kapasitas Fiskal dalam bentuk desimal (Berasal dari perhitungan internal indeks fiskal)
     * @return string Kategori Kapasitas Fiskal (Sangat Tinggi/Tinggi/Sedang/Rendah/Sangat Rendah)
     */
    public function hitungKapasitasFiskal(float $fiscal_ratio): string
    {
        if ($fiscal_ratio >= 1)
            return 'Sangat Tinggi';
        if ($fiscal_ratio >= 0.75)
            return 'Tinggi';
        if ($fiscal_ratio >= 0.5)
            return 'Sedang';
        if ($fiscal_ratio >= 0.25)
            return 'Rendah';
        return 'Sangat Rendah';
    }
}
