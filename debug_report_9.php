<?php

use App\Models\Report;
use App\Services\ReportQueryBuilder;
use App\Services\ColumnTransformationService;
use App\Services\Transformers\TransformerFactory;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$reportId = 9;
$report = Report::find($reportId);

if (!$report) {
    die("Report $reportId not found.\n");
}

echo "Report Found: " . $report->name . "\n";

// 1. Inspect Configurations
$transformations = $report->getColumnTransformations();
echo "\n--- Saved Transformations ---\n";
print_r($transformations);

// 2. Build Query & Get Data
$queryBuilder = new ReportQueryBuilder();
$sql = $queryBuilder->build($report->report_details);
echo "\n--- Generated SQL ---\n";
echo $sql . "\n";

$data = DB::select($sql . " LIMIT 1");
if (empty($data)) {
    die("No data returned from query.\n");
}

$firstRow = $data[0];
$firstRowArray = (array) $firstRow;
echo "\n--- First Row Data Keys ---\n";
print_r(array_keys($firstRowArray));

// 3. Test Transformation Service Logic
echo "\n--- Testing Transformation Application ---\n";
$factory = new TransformerFactory();
$service = new ColumnTransformationService($factory);

// Mock the finding logic specifically to see what happens
foreach ($transformations as $configKey => $config) {
    echo "Checking Config Key: '$configKey'\n";
    
    // Manual Check of matching logic
    $foundKey = null;
    $row = $firstRowArray;
    $searchKey = $configKey;
    
    // LOGIC REPLICATION
    if (array_key_exists($searchKey, $row)) {
        $foundKey = $searchKey;
        echo " -> Exact Match Found\n";
    }
    elseif (array_key_exists(str_replace('.', '_', $searchKey), $row)) {
        $foundKey = str_replace('.', '_', $searchKey);
        echo " -> Underscore Match Found: $foundKey\n";
    }
    else {
        $prefixMatch = false;
        foreach (array_keys($row) as $key) {
            if (str_ends_with($key, "_{$searchKey}") || $key === $searchKey) {
                $foundKey = $key;
                $prefixMatch = true;
                echo " -> Suffix Match Found: $foundKey\n";
                break;
            }
        }
        
        if (!$prefixMatch && str_contains($searchKey, '.')) {
             $parts = explode('.', $searchKey);
             $columnName = end($parts);
             if (array_key_exists($columnName, $row)) {
                 $foundKey = $columnName;
                 echo " -> Column Name Fallback Match Found: $foundKey\n";
             }
        }
    }
    
    if (!$foundKey) {
        echo " -> NO MATCH FOUND!\n";
    } else {
        // Try transform
        echo " -> Applying transformations...\n";
        $original = $row[$foundKey];
        $transformers = $config['transformers'] ?? [];
        foreach ($transformers as $tConfig) {
             echo "    -> Applying " . $tConfig['name'] . "\n";
             $original = $factory->transform($tConfig['name'], $original, $tConfig['options'] ?? []);
        }
        echo "    -> Result: $original\n";
    }
}
