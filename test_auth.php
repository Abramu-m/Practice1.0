<?php
require __DIR__ . '/vendor/autoload.php';
use Carbon\Carbon;

function formatAuthorizationStatus($latestAuth)
{
    if (empty($latestAuth)) return null;

    $facility = trim($latestAuth);
    $dateFormatted = 'N/A';

    if (preg_match('/^(.*?)\s+on\s+([A-Za-z]+\s+\d{1,2},\s*\d{4})/i', $latestAuth, $m)) {
        $facility = trim($m[1]);
        try {
            $dateFormatted = Carbon::parse($m[2])->format('Y-m-d');
        } catch (Exception $e) {
            $dateFormatted = 'N/A';
        }
    } elseif (preg_match('/^(.*?)\s+on\s+(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $latestAuth, $m)) {
        $facility = trim($m[1]);
        try {
            $dateFormatted = Carbon::parse($m[2])->format('Y-m-d');
        } catch (Exception $e) {
            $dateFormatted = 'N/A';
        }
    }

    return 'Facility: ' . $facility . '; Date: ' . $dateFormatted . '; Status: Accepted;';
}

$sample = 'Nyangao St.Walburgs on August 11,2025';
echo formatAuthorizationStatus($sample) . PHP_EOL;
