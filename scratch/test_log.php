<?php
$lines = file('storage/logs/laravel.log');
foreach($lines as $line) {
    if (strpos($line, 'Updating Budget Reals for Gov') !== false) {
        preg_match('/\"years\":\[(.*?)\]/', $line, $matches);
        if(isset($matches[1])) {
            echo 'Found request with years: ' . $matches[1] . PHP_EOL;
        }
    }
}
