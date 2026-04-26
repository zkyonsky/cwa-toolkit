<?php

namespace App\Services;

class QuantitativeRatingService
{

    private array $bobot = [
        'pdrb' => 20,
        'pengangguran' => 5,
        'kualitas_pembangunan_daerah' => 9,
        'kemandirian_anggaran' => 12,
        'kapasitas_fiskal' => 11,
        'penghasilan_menutupi_belanja' => 9,
        'efektifitas_belanja' => 6,
        'kualitas_penyusunan_anggaran' => 4,
        'beban_utang' => 10,
        'likuiditas' => 9,
        'kualitas_pencatatan_keuangan' => 5,
    ];

    private $indicative_rating;

    public function __construct()
    {
        $this->indicative_rating = new \App\Services\IndicativeRatingService();
    }

    public function calculate($skor_perkapita, $kategori_konsentrasi, $pengangguran, $ipm, $skor_pad_pendapatan, $kategori_volatilitas_pad_pendapatan, $operasi_pendapatan, $skor_modal_belanja, $skor_pegawai_belanja, $pad_tiga_tahun, $skor_utang_pendapatan, $skor_utang_pdrb, $skor_dscr, $skor_ds_pendapatan, $kategori_kapasitas_fiskal, $kategori_pemda, $syarat_minimum, $frekuensi_wtp)
    {
        $bobot = $this->bobot;
        $ind = $this->indicative_rating;

        // Kumpulkan proporsi skor ekonomi
        $ekonomi = [
            $this->pdrb($skor_perkapita, $kategori_konsentrasi) * $bobot['pdrb'],
            $ind->hitungKategoriTingkatPengangguran($pengangguran)['rasio'] * $bobot['pengangguran'],
            $ind->hitungKategoriIPM($ipm)['rasio'] * $bobot['kualitas_pembangunan_daerah'],
        ];

        // Kumpulkan proporsi skor keuangan
        $keuangan = [
            $this->kemandirianAnggaran($skor_pad_pendapatan, $kategori_volatilitas_pad_pendapatan) * $bobot['kemandirian_anggaran'],
            $ind->hitungOperasiPendapatan($operasi_pendapatan)['rasio'] * $bobot['penghasilan_menutupi_belanja'],
            $this->efektifitasBelanja($skor_modal_belanja, $skor_pegawai_belanja) * $bobot['efektifitas_belanja'],
            $ind->hitungPadTigaTahun($pad_tiga_tahun)['rasio'] * $bobot['kualitas_penyusunan_anggaran'],
            $this->bebanUtang($skor_utang_pendapatan, $skor_utang_pdrb) * $bobot['beban_utang'],
            $this->likuiditas($skor_dscr, $skor_ds_pendapatan) * $bobot['likuiditas'],
            $this->kapasitasFiskal($kategori_kapasitas_fiskal, $kategori_pemda) * $bobot['kapasitas_fiskal'],
            $this->kualitas_pencatatan_keuangan($syarat_minimum, $frekuensi_wtp) * $bobot['kualitas_pencatatan_keuangan'],
        ];

        // Kalkulasi pembagi (sum of weights)
        $total_bobot_ekonomi = $bobot['pdrb'] + $bobot['pengangguran'] + $bobot['kualitas_pembangunan_daerah'];
        $total_bobot_keuangan = array_sum($bobot) - $total_bobot_ekonomi;

        // Hitung skor akhir
        $skor_ekonomi = array_sum($ekonomi) / $total_bobot_ekonomi;
        $skor_keuangan = array_sum($keuangan) / $total_bobot_keuangan;
        $skor_kuantitatif = array_sum($ekonomi) + array_sum($keuangan);

        // Tentukan peringkat berdasarkan kriteria skor
        $peringkat = match (true) {
            $skor_kuantitatif >= 385 => 1,
            $skor_kuantitatif >= 340 => 2,
            $skor_kuantitatif >= 300 => 3,
            $skor_kuantitatif >= 260 => 4,
            default => 5,
        };

        return [
            'skor_ekonomi' => $skor_ekonomi,
            'skor_keuangan' => $skor_keuangan,
            'skor_kuantitatif' => $skor_kuantitatif,
            'peringkat' => $peringkat
        ];
    }

    public function pdrb(float $skor_perkapita, string $kategori_konsentrasi): int
    {
        $skor = (int) $skor_perkapita;

        if ($kategori_konsentrasi === 'Tinggi') {
            return max(1, $skor - 1);
        }

        // Untuk kategori Konsentrasi "Sedang" atau "Rendah"
        return max(1, min(5, $skor));
    }
    public function kemandirianAnggaran(float $skor_pad_pendapatan, string $kategori_volatilitas_pad_pendapatan): int
    {
        $skor = (int) $skor_pad_pendapatan;

        if ($kategori_volatilitas_pad_pendapatan === 'Volatil') {
            if ($skor >= 5)
                return 3;
            if ($skor >= 3)
                return 2;
            return 1;
        }

        return $skor;
    }
    public function efektifitasBelanja(int $skor_modal_belanja, int $skor_pegawai_belanja): int
    {
        $pegawai = (int) $skor_pegawai_belanja;
        $modal = (int) $skor_modal_belanja;

        $matrix = [
            5 => [5 => 5, 4 => 5, 3 => 4, 2 => 4, 1 => 3],
            4 => [5 => 5, 4 => 4, 3 => 4, 2 => 3, 1 => 2],
            3 => [5 => 4, 4 => 3, 3 => 3, 2 => 2, 1 => 2],
            2 => [5 => 3, 4 => 2, 3 => 2, 2 => 1, 1 => 1],
            1 => [5 => 2, 4 => 2, 3 => 1, 2 => 1, 1 => 1],
        ];

        return $matrix[$pegawai][$modal] ?? 1;
    }
    public function bebanUtang(int $skor_utang_pendapatan, int $skor_utang_pdrb): int
    {
        $utang_pendapatan = (int) $skor_utang_pendapatan;
        $utang_pdrb = (int) $skor_utang_pdrb;

        $matrix = [
            5 => [5 => 5, 4 => 4, 3 => 3, 2 => 2, 1 => 1],
            4 => [5 => 4, 4 => 4, 3 => 3, 2 => 2, 1 => 1],
            3 => [5 => 3, 4 => 3, 3 => 3, 2 => 2, 1 => 1],
            2 => [5 => 2, 4 => 2, 3 => 2, 2 => 2, 1 => 1],
            1 => [5 => 1, 4 => 1, 3 => 1, 2 => 1, 1 => 1],
        ];

        return $matrix[$utang_pendapatan][$utang_pdrb] ?? 1;
    }
    public function likuiditas(int $skor_dscr, int $skor_ds_pendapatan): int
    {
        $dscr = (int) $skor_dscr;
        $ds_pendapatan = (int) $skor_ds_pendapatan;

        $matrix = [
            5 => [5 => 5, 4 => 5, 3 => 5, 2 => 4, 1 => 3],
            3 => [5 => 3, 4 => 3, 3 => 3, 2 => 2, 1 => 2],
            2 => [5 => 2, 4 => 2, 3 => 2, 2 => 2, 1 => 1],
            1 => [5 => 1, 4 => 1, 3 => 1, 2 => 1, 1 => 1],
        ];

        return $matrix[$dscr][$ds_pendapatan] ?? 1;
    }
    public function kapasitasFiskal(string $kategori_kapasitas_fiskal, string $kategori_pemda): int
    {
        $matrix = [
            'Sangat Rendah' => ['Provinsi' => 2, 'Kabupaten/Kota' => 1],
            'Rendah' => ['Provinsi' => 2, 'Kabupaten/Kota' => 1],
            'Sedang' => ['Provinsi' => 3, 'Kabupaten/Kota' => 2],
            'Tinggi' => ['Provinsi' => 4, 'Kabupaten/Kota' => 3],
            'Sangat Tinggi' => ['Provinsi' => 5, 'Kabupaten/Kota' => 3],
        ];

        return $matrix[$kategori_kapasitas_fiskal][$kategori_pemda] ?? 1;
    }
    public function kualitas_pencatatan_keuangan(string $syarat_minimum, int $frekuensi_wtp): int
    {
        $matrix = [
            'Memenuhi syarat minimum' => [3 => 5, 2 => 4, 1 => 3, 0 => 2],
            'Tidak memenuhi syarat minimum' => [3 => 1, 2 => 1, 1 => 1, 0 => 1],
        ];

        return $matrix[$syarat_minimum][$frekuensi_wtp] ?? 1;
    }

}
