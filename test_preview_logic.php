<?php

use App\Http\Controllers\ReportTransformationController;
use App\Services\Transformers\TransformerFactory;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Preview Logic...\n";

$factory = new TransformerFactory();
$controller = new ReportTransformationController($factory);

// Mock Request Data
$requestData = [
    'value' => 'hello world',
    'transformers' => [
        ['name' => 'uppercase', 'options' => []],
        ['name' => 'truncate', 'options' => ['length' => 5]]
    ]
];

$request = Request::create('/preview', 'POST', $requestData);

try {
    $response = $controller->preview($request);
    $content = json_decode($response->getContent(), true);
    
    echo "Original: " . $content['original'] . "\n";
    echo "Transformed: " . $content['transformed'] . "\n";
    
    if ($content['transformed'] === 'HELLO...') { // UPPERCASE -> HELLO WORLD -> TRUNCATE 5 -> HELLO...
        echo "SUCCESS: Preview logic works correctly with chain!\n";
    } else {
        echo "FAILURE: Unexpected result.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
