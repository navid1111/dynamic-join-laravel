<?php

use App\Services\Transformers\TransformerFactory;
use App\Services\ColumnTransformationService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$factory = new TransformerFactory();
$service = new ColumnTransformationService($factory);

// Scenario: Report config uses dot notation (as saved by frontend)
$transformations = [
    'users.name' => [ // Key as saved in DB
        'transformers' => [
            ['name' => 'uppercase']
        ]
    ]
];

// Scenario: Database results use underscore alias
$data = [
    (object) [
        'users_name' => 'john doe', // SQL Alias
        'users_email' => 'john@example.com'
    ]
];

echo "Testing Transformation Application...\n";
$result = $service->applyTransformations($data, $transformations);

$transformedName = $result[0]->users_name;
echo "Original: 'john doe'\n";
echo "Transformed: '$transformedName'\n";

if ($transformedName === 'JOHN DOE') {
    echo "SUCCESS: Transformation applied despite key mismatch!\n";
} else {
    echo "FAILURE: Transformation not applied.\n";
}
