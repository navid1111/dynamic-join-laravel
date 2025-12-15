<?php

require __DIR__ . '/vendor/autoload.php';

// Mock Service logic
function findActualColumnKey(array $row, string $searchKey): ?string
{
    // Current Logic
    if (array_key_exists($searchKey, $row)) {
        return $searchKey;
    }
    
    $underscoreKey = str_replace('.', '_', $searchKey);
    if (array_key_exists($underscoreKey, $row)) {
        return $underscoreKey;
    }

    foreach (array_keys($row) as $key) {
        if (str_ends_with($key, "_{$searchKey}") || $key === $searchKey) {
            return $key;
        }
    }
    
    // Logic Missing: Column Only?
    
    return null;
}

// Test Data
$rowUnaliased = ['name' => 'John', 'email' => 'john@test.com'];
$configKey = 'users.name';

echo "Testing Config 'users.name' against Row ['name' => 'John']...\n";
$match = findActualColumnKey($rowUnaliased, $configKey);
echo "Match: " . ($match ?? 'NULL') . "\n";

if ($match === 'name') {
    echo "SUCCESS\n";
} else {
    echo "FAILURE: Current logic fails to find 'name'\n";
}
