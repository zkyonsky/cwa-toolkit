<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Financing;

class FinancingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $financings = [
            ["gov_code" => "1171", "os_debt" => "9291822103", "lender" => "SMI"],
            ["gov_code" => "1175", "os_debt" => "65756704105", "lender" => "SMI"],
            ["gov_code" => "1219", "os_debt" => "51649131675", "lender" => "SMI"],
            ["gov_code" => "1220", "os_debt" => "127408905208.1", "lender" => "SMI"],
            ["gov_code" => "1218", "os_debt" => "70659233100", "lender" => "SMI"],
            ["gov_code" => "1204", "os_debt" => "52376508637", "lender" => "SMI"],
            ["gov_code" => "1205", "os_debt" => "242715554515.63", "lender" => "SMI"],
            ["gov_code" => "1271", "os_debt" => "67188494476", "lender" => "SMI"],
            ["gov_code" => "1371", "os_debt" => "25133455106.85", "lender" => "SMI"],
            ["gov_code" => "1372", "os_debt" => "70648108564", "lender" => "SMI"],
            ["gov_code" => "1508", "os_debt" => "106625253354", "lender" => "SMI"],
            ["gov_code" => "1607", "os_debt" => "155458138346", "lender" => "SMI"],
            ["gov_code" => "1674", "os_debt" => "62019286252", "lender" => "SMI"],
            ["gov_code" => "1671", "os_debt" => "11039320377.6", "lender" => "SMI"],
            ["gov_code" => "1801", "os_debt" => "34342951595", "lender" => "SMI"],
            ["gov_code" => "1803", "os_debt" => "55581331537", "lender" => "SMI"],
            ["gov_code" => "1805", "os_debt" => "63744325294", "lender" => "SMI"],
            ["gov_code" => "1806", "os_debt" => "63094430095", "lender" => "SMI"],
            ["gov_code" => "1802", "os_debt" => "59893284056", "lender" => "SMI"],
            ["gov_code" => "1812", "os_debt" => "51213007734", "lender" => "SMI"],
            ["gov_code" => "1871", "os_debt" => "114929858006.15", "lender" => "SMI"],
            ["gov_code" => "19", "os_debt" => "58991481289", "lender" => "SMI"],
            ["gov_code" => "31", "os_debt" => "5334159896268", "lender" => "SMI"],
            ["gov_code" => "32", "os_debt" => "2491126748111", "lender" => "SMI"],
            ["gov_code" => "3271", "os_debt" => "23585806872", "lender" => "SMI"],
            ["gov_code" => "3302", "os_debt" => "112886624267", "lender" => "SMI"],
            ["gov_code" => "35", "os_debt" => "36706484545", "lender" => "SMI"],
            ["gov_code" => "3528", "os_debt" => "19499494966", "lender" => "SMI"],
            ["gov_code" => "3502", "os_debt" => "110306985200", "lender" => "SMI"],
            ["gov_code" => "3527", "os_debt" => "100422770445", "lender" => "SMI"],
            ["gov_code" => "3503", "os_debt" => "108194215447", "lender" => "SMI"],
            ["gov_code" => "3576", "os_debt" => "57252609949", "lender" => "SMI"],
            ["gov_code" => "36", "os_debt" => "521955203325", "lender" => "SMI"],
            ["gov_code" => "51", "os_debt" => "1095591162869", "lender" => "SMI"],
            ["gov_code" => "5106", "os_debt" => "59813817597", "lender" => "SMI"],
            ["gov_code" => "5104", "os_debt" => "337954756319", "lender" => "SMI"],
            ["gov_code" => "5105", "os_debt" => "51241073455", "lender" => "SMI"],
            ["gov_code" => "5102", "os_debt" => "86911062007", "lender" => "SMI"],
            ["gov_code" => "52", "os_debt" => "603059591244", "lender" => "SMI"],
            ["gov_code" => "5202", "os_debt" => "164878644490", "lender" => "SMI"],
            ["gov_code" => "5203", "os_debt" => "16512030436.94", "lender" => "SMI"],
            ["gov_code" => "53", "os_debt" => "793006327780", "lender" => "SMI"],
            ["gov_code" => "5308", "os_debt" => "161744086862.66", "lender" => "SMI"],
            ["gov_code" => "5315", "os_debt" => "187651129129", "lender" => "SMI"],
            ["gov_code" => "5310", "os_debt" => "146676037397", "lender" => "SMI"],
            ["gov_code" => "6102", "os_debt" => "170170206438", "lender" => "SMI"],
            ["gov_code" => "6172", "os_debt" => "125000978788.93", "lender" => "SMI"],
            ["gov_code" => "6203", "os_debt" => "13235419310", "lender" => "SMI"],
            ["gov_code" => "6309", "os_debt" => "58847676197", "lender" => "SMI"],
            ["gov_code" => "6305", "os_debt" => "14707792076", "lender" => "SMI"],
            ["gov_code" => "6409", "os_debt" => "68913304031", "lender" => "SMI"],
            ["gov_code" => "71", "os_debt" => "864762300255", "lender" => "SMI"],
            ["gov_code" => "7103", "os_debt" => "174160648953", "lender" => "SMI"],
            ["gov_code" => "7102", "os_debt" => "64318465200", "lender" => "SMI"],
            ["gov_code" => "7172", "os_debt" => "202071218802", "lender" => "SMI"],
            ["gov_code" => "7171", "os_debt" => "193647658750", "lender" => "SMI"],
            ["gov_code" => "7173", "os_debt" => "79933848005", "lender" => "SMI"],
            ["gov_code" => "7212", "os_debt" => "118021345497", "lender" => "SMI"],
            ["gov_code" => "7204", "os_debt" => "12162232282", "lender" => "SMI"],
            ["gov_code" => "73", "os_debt" => "524355343703", "lender" => "SMI"],
            ["gov_code" => "7311", "os_debt" => "236177572900.16", "lender" => "SMI"],
            ["gov_code" => "7316", "os_debt" => "309829651176", "lender" => "SMI"],
            ["gov_code" => "7306", "os_debt" => "118670001215", "lender" => "SMI"],
            ["gov_code" => "7322", "os_debt" => "123146708436", "lender" => "SMI"],
            ["gov_code" => "7307", "os_debt" => "45429568549", "lender" => "SMI"],
            ["gov_code" => "7312", "os_debt" => "105217413798", "lender" => "SMI"],
            ["gov_code" => "7305", "os_debt" => "213893762962", "lender" => "SMI"],
            ["gov_code" => "74", "os_debt" => "436773181152", "lender" => "SMI"],
            ["gov_code" => "7409", "os_debt" => "125154663062", "lender" => "SMI"],
            ["gov_code" => "7405", "os_debt" => "235454419746", "lender" => "SMI"],
            ["gov_code" => "7402", "os_debt" => "159110056678", "lender" => "SMI"],
            ["gov_code" => "7471", "os_debt" => "293473373623", "lender" => "SMI"],
            ["gov_code" => "75", "os_debt" => "93144626321", "lender" => "SMI"],
            ["gov_code" => "7504", "os_debt" => "116351407363", "lender" => "SMI"],
            ["gov_code" => "7502", "os_debt" => "308700047582.55", "lender" => "SMI"],
            ["gov_code" => "7505", "os_debt" => "94840178537", "lender" => "SMI"],
            ["gov_code" => "7503", "os_debt" => "129176967950.3", "lender" => "SMI"],
            ["gov_code" => "7571", "os_debt" => "193255618072.09", "lender" => "SMI"],
            ["gov_code" => "76", "os_debt" => "182948894747", "lender" => "SMI"],
            ["gov_code" => "7603", "os_debt" => "72335281160.97", "lender" => "SMI"],
            ["gov_code" => "81", "os_debt" => "410016540210", "lender" => "SMI"],
            ["gov_code" => "8108", "os_debt" => "152703989871", "lender" => "SMI"],
            ["gov_code" => "82", "os_debt" => "70938154617", "lender" => "SMI"],
            ["gov_code" => "8201", "os_debt" => "182665660016", "lender" => "SMI"],
            ["gov_code" => "8207", "os_debt" => "132829753948", "lender" => "SMI"],
            ["gov_code" => "9408", "os_debt" => "143224896340", "lender" => "SMI"],

        ];

        foreach ($financings as $data) {
            Financing::updateOrCreate(
                ['gov_code' => $data['gov_code']],
                [
                    'os_debt' => $data['os_debt'],
                    'lender' => $data['lender'],
                ]
            );
        }
    }
}
