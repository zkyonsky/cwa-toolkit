<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UploadDataController extends Controller
{
    public function index()
    {
        return Inertia::render('UploadData/Index');
    }

    public function downloadTemplate($type)
    {
        $table = $this->getTableName($type);
        if (!$table) {
            abort(404, "Invalid type");
        }

        $columns = Schema::getColumnListing($table);
        // Exclude some columns like id, created_at, updated_at
        $exclude = ['id', 'created_at', 'updated_at'];
        $headers = array_values(array_diff($columns, $exclude));

        $fileName = "{$type}_template.csv";
        
        $headersLine = implode(',', $headers) . "\n";

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
            return redirect()->back()->with('error', 'Invalid type');
        }

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV file is empty or invalid.');
        }

        $header = array_map('trim', $header);
        
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
                } else if (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                }

                $data = array_combine($header, $row);
                
                // For safety, remove any empty string values to null if they are numeric fields or generally empty
                foreach($data as $k => $v) {
                    if ($v === '') $data[$k] = null;
                }

                DB::table($table)->insert($data);
                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return redirect()->back()->with('error', 'Error importing data: ' . $e->getMessage());
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
