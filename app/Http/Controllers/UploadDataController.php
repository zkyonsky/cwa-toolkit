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
            'gov_code', 'year', 'income_after_cleansing', 'pad_after_cleansing',
            'local_tax', 'local_retribution', 'separated_asset_management_results',
            'other_legitimate_pad', 'transfer_income', 'general_allocation_fund',
            'special_allocation_fund', 'profit_sharing_fund', 'other_legitimate_income',
            'spending_after_cleansing', 'operational_spending', 'employee_spending',
            'capital_spending', 'other_spending', 'grant_spending',
            'social_assistance_spending', 'fix_asset_spending', 'other_fix_asset_spending',
            'total_transfer',
        ],
        'budget_plans' => [
            'gov_code', 'year', 'self_revenue', 'underground_water_tax',
            'street_lighting_tax', 'electricity_tax', 'opsen_vehicle_tax',
            'blud_revenue', 'cigarette_tax', 'shared_cigarette_tax',
            'vehicle_tax', 'shared_vehicle_tax', 'general_allocation_fund',
            'general_allocation_fund_education', 'general_allocation_fund_health',
            'general_allocation_fund_public_work', 'general_allocation_fund_district',
            'profit_sharing_fund', 'profit_sharing_fund_cigarette',
            'profit_sharing_fund_sawit', 'profit_sharing_fund_reboisation',
            'add_profit_sharing_fund_oli_gas_otsus', 'special_autonomy',
            'inter_regional_transfer_revenue', '10_percent_shared_vehicle_tax',
            '50_percent_shared_cigarette_tax', 'other_revenue', 'central_gov_grant',
            'national_health_revenue', 'sharing_fund_spending', 'village_fund_allocation',
            'employee_spending', 'teacher_non_certification_allowance',
            'teacher_certification_allowance', 'regional_teacher_additional_allowance',
            'p3k_allowance', 'regional_hospital_retribution', 'availability_payment',
        ],
        'economy_indicators' => [
            'gov_code', 'year', 'population', 'poverty_rate', 'unemployment_rate',
            'gdp_growth', 'inflation', 'gdp',
        ],
        'sectoral_gdps' => [
            'gov_code', 'year', 'agriculture_forestry_fishery', 'mining_quarrying',
            'processing_industry', 'electricity_gas', 'water_supply_waste',
            'construction', 'wholesale_retail_trade', 'transportation_warehousing',
            'accommodation_food_beverage', 'information_communication',
            'financial_insurance', 'real_estate', 'company_service',
            'government_defense_social_security', 'education_service',
            'health_social_work', 'other_service',
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

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
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

                // Convert empty strings to null
                foreach ($data as $k => $v) {
                    if ($v === '') {
                        $data[$k] = null;
                    }
                }

                DB::table($table)->insert($data);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            Log::error("CSV import failed for {$type}", [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return redirect()->back()->with('error', 'Error importing data. Please check your CSV format and try again.');
        }

        fclose($handle);

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
