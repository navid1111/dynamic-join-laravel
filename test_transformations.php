<?php

use App\Services\Transformers\TransformerFactory;
use App\Services\ColumnTransformationService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Transformer Factory...\n";
$factory = new TransformerFactory();
$transformers = $factory->getAllTransformers();
echo "Registered Transformers: " . count($transformers) . "\n";
foreach ($transformers as $name => $t) {
    echo " - $name\n";
}

echo "\nTesting ColumnTransformationService...\n";
$service = new ColumnTransformationService($factory);

$data = [
    (object)['name' => 'John Doe', 'date' => '2024-03-15', 'amount' => 1234.56, 'status' => 'active'],
    (object)['name' => 'jane doe', 'date' => '2024-06-20', 'amount' => 500, 'status' => 'inactive'],
];

$transformations = [
    'name' => ['transformers' => [['name' => 'uppercase']]],
    'date' => ['transformers' => [['name' => 'extract_year']]],
    'amount' => ['transformers' => [['name' => 'format_currency', 'options' => ['currency' => 'EUR']]]],
    'status' => ['transformers' => [['name' => 'status_badge']]],
];

$transformed = $service->applyTransformations($data, $transformations);

echo "\nOriginal Data (Row 1):\n";
print_r($data[0]);

echo "\nTransformed Data (Row 1):\n";
print_r($transformed[0]);

if ($transformed[0]->name === 'JOHN DOE' && 
    $transformed[0]->date === 2024 &&
    str_contains($transformed[0]->amount, '€') &&
    str_contains($transformed[0]->status, 'badge')) {
    echo "\nSUCCESS: Transformations applied correctly!\n";
} else {
    echo "\nFAILURE: Transformations mismatch.\n";
}
