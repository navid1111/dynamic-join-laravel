<?php

namespace App\Services\Transformers\NumberTransformers;

use App\Services\Transformers\ColumnTransformerInterface;

class PercentageTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (!is_numeric($value)) {
            return $value;
        }
        
        $decimals = $options['decimals'] ?? 2;
        $multiply = $options['multiply_by_100'] ?? true;
        
        $percentage = $multiply ? $value * 100 : $value;
        
        return number_format($percentage, $decimals) . '%';
    }
    
    public function getName(): string
    {
        return 'percentage';
    }
    
    public function getDescription(): string
    {
        return 'Format as percentage (e.g., 0.75 → "75.00%")';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'decimals',
                'type' => 'number',
                'label' => 'Decimal Places',
                'default' => 2,
            ],
            [
                'name' => 'multiply_by_100',
                'type' => 'boolean',
                'label' => 'Multiply by 100 (0.75 → 75%)',
                'default' => true,
            ],
        ];
    }
}
