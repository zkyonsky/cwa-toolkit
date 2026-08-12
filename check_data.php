<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$gov = App\Models\Gov::where('name', 'Aceh')->first();
$assessment = App\Models\Assessment::whereHas('assessee', function($q) use ($gov) {
    $q->where('gov_id', $gov->id);
})->first();

echo 'Assessment Date: ' . ($assessment ? $assessment->date : 'none') . "\n";
echo 'Gov Code: ' . $gov->code . "\n";
echo 'Budget_real Years: ' . json_encode(App\Models\Budget_real::where('gov_code', $gov->code)->pluck('year')) . "\n";
