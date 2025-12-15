<?php

use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Create Report with Transformations...\n";

// Mock Data simulating form submission
$reportName = 'Test Transformed Report ' . time();
$users = ['User1'];
$tableName = 'users'; // Assuming 'users' table exists
$columns = ['users' => ['name', 'email']]; // Format from form might differ, but controller logic handles `report_details`

// transformation data format: transformations[table_column][transformers][0][name]
$transformations = [
    'users_name' => [
        'transformers' => [
            ['name' => 'uppercase']
        ]
    ]
];

// Simulate Controller Logic (since we can't easily make a HTTP request in this script without full app boot)
// Effectively testing repository/model saving logic
try {
    $report = Report::create([
        'name' => $reportName,
        'users' => $users,
        'report_details' => [
            'tables' => [$tableName],
            'columns' => $columns
             // Note: actual structure depends on how `processForm` handles `except`. 
             // Controller: $data = $request->except(['_token', 'table', 'users', 'name', 'transformations']);
             // So `report_details` gets everything else.
        ],
        'column_transformations' => $transformations
    ]);

    echo "Report created with ID: " . $report->id . "\n";
    
    // Verify Persistence
    $savedReport = Report::find($report->id);
    $savedTransformations = $savedReport->column_transformations;
    
    echo "Saved Transformations:\n";
    print_r($savedTransformations);
    
    if (isset($savedTransformations['users_name']['transformers'][0]['name']) && 
        $savedTransformations['users_name']['transformers'][0]['name'] === 'uppercase') {
        echo "SUCCESS: Transformations saved correctly!\n";
    } else {
        echo "FAILURE: Transformations not saved as expected.\n";
    }

    // Cleanup
    $savedReport->delete();
    echo "Test Report Deleted.\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
