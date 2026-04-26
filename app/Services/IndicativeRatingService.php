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
     * @param float $pdrb PDRB HB per Kapita dalam satuan Ribu Rupiah
     * @return array Kategori PDRB dan Rasio
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

    public function hitungKonsentrasiPDRB(float $pdrb): string
    {
        if ($pdrb >= 2500)
            return "Tinggi";
        if ($pdrb >= 1500)
            return "Sedang";
        return "Rendah";
    }

    public function hitungPerbandinganPDRB(float $pdrb, float $pdb_indonesia): string
    {
        return $pdrb < (1.1 * $pdb_indonesia) ? "Dibawah Nasional" : "Sama atau lebih tinggi dibanding Nasional";
    }

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

    public function hitungVolatilPad(float $volatil_pad): string
    {
        return ($volatil_pad < 50) ? "Tidak Volatil" : "Volatil";
    }

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
