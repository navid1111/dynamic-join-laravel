<?php

use App\Services\Transformers\TransformerFactory;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$factory = new TransformerFactory();
$transformers = $factory->getTransformersByCategory();

echo "Count of categories: " . count($transformers) . "\n";
foreach ($transformers as $category => $items) {
    echo "Category: $category - Items: " . count($items) . "\n";
    foreach ($items as $name => $transformer) {
        echo " - Name: $name, Description: " . $transformer->getDescription() . "\n";
    }
}
