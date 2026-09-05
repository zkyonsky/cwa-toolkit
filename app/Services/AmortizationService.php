<?php

namespace App\Services;
use DateTime;



class AmortizationService
{

    /**
     * Menghitung simulasi pinjaman dengan pencairan bertahap otomatis dan biaya provisi
     * * @param float $plafon Total limit pinjaman
     * @param float $bungaTahunan Suku bunga tahunan (%)
     * @param int $tenorPinjaman Jangka waktu pengembalian setelah cair (bulan)
     * @param int $masaPencairan Jangka waktu proses pencairan (bulan)
     * @param string $tglMulai Tanggal mulai pencairan pertama
     * @return array
     */
    function hitungPinjamanProgresif($plafon, $bungaTahunan, $tenorPinjaman, $masaPencairan, $tglMulai)
    {
        // 1. Perhitungan Dasar & Parameter Simulasi
        $totalMonths = (int)$tenorPinjaman; 
        $apMonths = (int)$masaPencairan;
        
        if ($totalMonths <= 0 || $apMonths <= 0 || empty($tglMulai)) {
            return [
                'parameter' => [
                    'total_plafon'      => $plafon,
                    'masa_pencairan'    => $apMonths . " bulan",
                    'pencairan_bulanan' => 0,
                    'biaya_provisi'     => 0,
                    'total_bunga'       => 0,
                ],
                'jadwal_lengkap'        => [],
                'hasil_cicilan' => [
                    'cicilan_per_bulan'  => 0,
                    'rata_pokok_tahunan' => 0,
                    'rata_bunga_tahunan' => 0,
                    'total_bayar_akhir'  => 0,
                    'tanggal_cicilan_1'  => null
                ]
            ];
        }
        $graceMonths = 6; // Masa Tenggang setelah AP selesai sebelum Pokok dimulai (berdasarkan tabel)
        $repaymentMonths = $totalMonths - $apMonths - $graceMonths;
        
        if ($repaymentMonths <= 0) {
            // Fallback jika tenor terlalu pendek
            $graceMonths = 0;
            $repaymentMonths = $totalMonths - $apMonths;
        }

        if ($repaymentMonths <= 0) {
            $repaymentMonths = 1; // Prevent division by zero
        }

        $pencairanPerBulan = $plafon / $apMonths;
        $pokokPerBulan = $plafon / $repaymentMonths;
        $biayaProvisi = $plafon * 0.01;

        $currentOS = 0;
        $totalBunga = 0;
        $schedule = [];
        
        // Inisialisasi Tanggal
        $baseDate = new DateTime($tglMulai);
        $currentDate = clone $baseDate;

        for ($m = 1; $m <= $totalMonths; $m++) {
            $periodStartDate = clone $currentDate;
            
            // Menentukan Tanggal Akhir Periode
            if ($m == 1) {
                // Periode 1 berakhir di tanggal 1 bulan berikutnya
                $periodEndDate = (clone $currentDate)->modify('first day of next month');
            } elseif ($m == $totalMonths) {
                // Periode terakhir berakhir di tanggal anniversary
                $periodEndDate = (clone $baseDate)->modify('+' . ($totalMonths / 12) . ' years');
            } else {
                // Periode menengah selalu dari tanggal 1 ke tanggal 1
                $periodEndDate = (clone $currentDate)->modify('first day of next month');
            }
            
            $days = $periodStartDate->diff($periodEndDate)->days;

            // Alokasi Pencairan & Pokok
            $pencairan = ($m <= $apMonths) ? $pencairanPerBulan : 0;
            $pokok = ($m > ($apMonths + $graceMonths)) ? $pokokPerBulan : 0;
            
            // Penyesuaian bulan terakhir agar OS bersih
            if ($m == $totalMonths) {
                $pokok = $currentOS + $pencairan;
            }
            
            $osAkhir = $currentOS + $pencairan - $pokok;

            // Bunga = (Rate * Hari / 360) * Avg OS (sesuai verifikasi tabel)
            $avgOS = ($currentOS + $osAkhir) / 2;
            $bungaBulanIni = ($bungaTahunan / 100) * ($days / 360) * $avgOS;

            $schedule[] = [
                'periode'   => $m,
                'tgl_awal'  => $periodStartDate->format('Y-m-d'),
                'tgl_akhir' => $periodEndDate->format('Y-m-d'),
                'hari'      => $days,
                'os_awal'   => round($currentOS, 2),
                'pencairan' => round($pencairan, 2),
                'pokok'     => round($pokok, 2),
                'os_akhir'  => round($osAkhir, 2),
                'bunga'     => round($bungaBulanIni, 2)
            ];

            $totalBunga += $bungaBulanIni;
            $currentOS = $osAkhir;
            $currentDate = clone $periodEndDate;
        }

        // 4. Kalkulasi Rata-rata Tahunan untuk DSCR
        $tenorTahun = $totalMonths / 12;
        $rataPokokTahunan = $plafon / $tenorTahun;
        $rataBungaTahunan = $totalBunga / $tenorTahun;

        // Cicilan bulanan rata-rata (hanya selama masa repayment agar relevan dengan DSCR)
        $totalBayarRepayment = $plafon + ($totalBunga); // Estimasi
        $cicilanBulananRata = $totalBayarRepayment / $totalMonths;

        return [
            'parameter' => [
                'total_plafon'      => $plafon,
                'masa_pencairan'    => $apMonths . " bulan",
                'pencairan_bulanan' => round($pencairanPerBulan, 2),
                'biaya_provisi'     => $biayaProvisi,
                'total_bunga'       => round($totalBunga, 2),
            ],
            'jadwal_lengkap'        => $schedule,
            'hasil_cicilan' => [
                'cicilan_per_bulan'  => round($cicilanBulananRata, 2),
                'rata_pokok_tahunan' => round($rataPokokTahunan, 2),
                'rata_bunga_tahunan' => round($rataBungaTahunan, 2),
                'total_bayar_akhir'  => round($plafon + $totalBunga + $biayaProvisi, 2),
                'tanggal_cicilan_1'  => isset($schedule[$apMonths + $graceMonths]['tgl_awal']) ? $schedule[$apMonths + $graceMonths]['tgl_awal'] : null // Kapan pokok mulai dibayar
            ]
        ];
    }

    // --- CONTOH PENGGUNAAN ---
// $plafonPinjaman = 500000000; // 500 Juta
// $bunga          = 9;         // 9% per tahun
// $tenor          = 24;        // Cicilan 24 bulan
// $masaCair       = 5;         // Cair bertahap selama 5 bulan
// $tglAwal        = '2026-05-01';

    // $hasil = hitungPinjamanProgresif($plafonPinjaman, $bunga, $tenor, $masaCair, $tglAwal);

    // echo "BERKAS SIMULASI PINJAMAN\n";
// echo "====================================\n";
// echo "Total Plafon      : Rp " . number_format($hasil['parameter']['total_plafon'], 0, ',', '.') . "\n";
// echo "Pencairan/Bulan   : Rp " . number_format($hasil['parameter']['pencairan_bulanan'], 0, ',', '.') . "\n";
// echo "Biaya Provisi (1%): Rp " . number_format($hasil['parameter']['biaya_provisi'], 0, ',', '.') . "\n";
// echo "------------------------------------\n";
// echo "Cicilan per Bulan : Rp " . number_format($hasil['hasil_cicilan']['cicilan_per_bulan'], 0, ',', '.') . "\n";
// echo "Rata-rata Pokok/Th: Rp " . number_format($hasil['hasil_cicilan']['rata_pokok_tahunan'], 0, ',', '.') . "\n";
// echo "Rata-rata Bunga/Th: Rp " . number_format($hasil['hasil_cicilan']['rata_bunga_tahunan'], 0, ',', '.') . "\n";
// echo "------------------------------------\n";
// echo "Estimasi Cicilan Pertama: " . $hasil['hasil_cicilan']['tanggal_cicilan_1'] . "\n";
// 
}