<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UploadDataController extends Controller
{
    /**
     * Whitelisted columns per table to prevent CSV injection.
     * Only these columns are allowed during import.
     */
    private const ALLOWED_COLUMNS = [
        'budget_reals' => [
            'bpk_opinion',
            'input_status',
            'gov_code',
            'year',
            'income_after_cleansing',
            'pad_after_cleansing',
            'tax_income',
            'retribution_income',
            'asset_income',
            'other_pad',
            'transfer_income',
            'other_legitimate_income',
            'other_income',
            'spending_after_cleansing',
            'operational_spending',
            'employee_spending',
            'good_service_spending',
            'interest_spending',
            'subsidy_spending',
            'grant_spending',
            'social_spending',
            'capital_spending',
            'land_spending',
            'machine_spending',
            'building_spending',
            'infrastructure_spending',
            'other_asset_spending',
            'unexpected_spending',
            'other_fix_asset_spending',
            'total_transfer',
        ],
        'budget_plans' => [
            'gov_code',
            'year',
            'self_revenue',
            'vehicle_tax',
            'shared_vehicle_tax',
            'cigarette_tax',
            'shared_cigarette_tax',
            'underground_water_tax',
            'electricity_tax',
            'street_lighting_tax',
            'opsen_vehicle_tax',
            'blud_revenue',
            'regional_hospital_retribution',
            'general_allocation_fund',
            'general_allocation_fund_district',
            'general_allocation_fund_education',
            'general_allocation_fund_health',
            'general_allocation_fund_public_work',
            'profit_sharing_fund',
            'profit_sharing_fund_cigarette',
            'profit_sharing_fund_reboisation',
            'profit_sharing_fund_sawit',
            'add_profit_sharing_fund_oli_gas_otsus',
            'special_autonomy',
            'other_revenue',
            'central_gov_grant',
            'national_health_revenue',
            'sharing_fund_spending',
            'village_fund_allocation',
            'employee_spending',
            'p3k_allowance',
            'teacher_non_certification_allowance',
            'teacher_certification_allowance',
            'regional_teacher_additional_allowance',
            'inter_regional_transfer_revenue',
            '50_percent_shared_cigarette_tax',
            '10_percent_shared_vehicle_tax',
        ],
        'economy_indicators' => [
            'gov_code',
            'poverty',
            'unemployment',
            'gdp_growth',
            'gdp_perkapita',
            'hdci',
            'infras_real',
            'fiscal_ratio',
            'year',
            'gdp',
        ],
        'sectoral_gdps' => [
            'gov_code',
            'year',
            'agriculture_forestry_fishery',
            'mining_quarrying',
            'processing_industry',
            'electricity_gas',
            'water_waste',
            'contruction',
            'trade_vehicle_repair',
            'transportation_warehousing',
            'acomodation_food_beverage',
            'information_communication',
            'finance_insurance',
            'real_estate',
            'company_service',
            'gov_adm_defense_sosial_security',
            'education_service',
            'health_social_service',
            'other_service',
        ],
    ];

    public function index()
    {
        return Inertia::render('UploadData/Index');
    }

    public function downloadTemplate($type)
    {
        $table = $this->getTableName($type);
        if (!$table) {
            abort(404, 'Invalid type');
        }

        $allowedColumns = self::ALLOWED_COLUMNS[$type] ?? [];
        if (empty($allowedColumns)) {
            abort(404, 'Invalid type');
        }

        $fileName = "{$type}_template.csv";
        $headersLine = implode(',', $allowedColumns) . "\n";

        return response($headersLine)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }

    public function upload(Request $request, $type)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $table = $this->getTableName($type);
        if (!$table) {
            return redirect()->back()->with('error', 'Invalid type.');
        }

        $allowedColumns = self::ALLOWED_COLUMNS[$type] ?? [];
        if (empty($allowedColumns)) {
            return redirect()->back()->with('error', 'Invalid type.');
        }

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty or invalid.');
        }

        // Sanitize headers: trim whitespace and remove BOM characters
        $header = array_map(function ($col) {
            return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', trim($col));
        }, $header);

        // Validate all CSV headers are in the whitelist
        $invalidColumns = array_diff($header, $allowedColumns);
        if (!empty($invalidColumns)) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV contains invalid columns: ' . implode(', ', $invalidColumns) . '. Please use the provided template.');
        }

        $imported = 0;

        // Pre-read all rows so we can validate gov_code before inserting
        $rows = [];
        $rowNumber = 1; // 1-based (header is row 1)
        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (count(array_filter($row)) === 0) {
                continue;
            }

            // If row doesn't match header length, pad it or slice it
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), null);
            } elseif (count($row) > count($header)) {
                $row = array_slice($row, 0, count($header));
            }

            $data = array_combine($header, $row);

            // Only keep whitelisted columns (defense in depth)
            $data = array_intersect_key($data, array_flip($allowedColumns));

            // Convert empty strings and common empty placeholders to null
            foreach ($data as $k => $v) {
                $v = trim((string) $v);
                if ($v === '' || $v === '-') {
                    $data[$k] = null;
                }
            }

            $rows[] = ['row_number' => $rowNumber, 'data' => $data];
        }
        fclose($handle);

        // Validate gov_code foreign key if the CSV contains a gov_code column
        if (in_array('gov_code', $header)) {
            $csvGovCodes = collect($rows)
                ->pluck('data.gov_code')
                ->filter()
                ->unique()
                ->values();

            if ($csvGovCodes->isNotEmpty()) {
                $existingCodes = DB::table('govs')
                    ->whereIn('code', $csvGovCodes->all())
                    ->pluck('code')
                    ->map(fn ($c) => (string) $c);

                $invalidCodes = $csvGovCodes->diff($existingCodes);

                if ($invalidCodes->isNotEmpty()) {
                    // Collect row numbers per invalid gov_code
                    $invalidDetails = [];
                    foreach ($rows as $r) {
                        $code = $r['data']['gov_code'] ?? null;
                        if ($code !== null && $invalidCodes->contains($code)) {
                            $invalidDetails[$code][] = $r['row_number'];
                        }
                    }

                    $detailParts = [];
                    foreach ($invalidDetails as $code => $rowNums) {
                        $detailParts[] = "gov_code \"{$code}\" (baris " . implode(', ', $rowNums) . ')';
                    }

                    return redirect()->back()->with('error',
                        'Import gagal: gov_code berikut tidak ditemukan di tabel pemerintah daerah: '
                        . implode('; ', $detailParts)
                        . '. Pastikan semua gov_code sudah terdaftar sebelum import.'
                    );
                }
            }
        }

        DB::beginTransaction();
        try {
            foreach ($rows as $r) {
                DB::table($table)->insert($r['data']);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("CSV import failed for {$type}", [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $errorDetail = explode('(Connection:', $e->getMessage())[0];
            $errorMessage = 'Error importing data: ' . trim($errorDetail);

            if ($e->getCode() == 23000) {
                $errorMessage = 'Data failed to import due to a database constraint violation. Please check if the gov_code exists in the system. Details: ' . trim($errorDetail);
            }

            return redirect()->back()->with('error', $errorMessage);
        }

        return redirect()->back()->with('message', "{$imported} record(s) imported successfully into {$type}.");
    }

    private function getTableName($type)
    {
        $map = [
            'budget_reals' => 'budget_reals',
            'budget_plans' => 'budget_plans',
            'economy_indicators' => 'economy_indicators',
            'sectoral_gdps' => 'sectoral_gdps',
        ];
        return $map[$type] ?? null;
    }
}
