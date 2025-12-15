<?php

namespace App\Services\Transformers\NumberTransformers;

use App\Services\Transformers\ColumnTransformerInterface;

class FormatNumberTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (!is_numeric($value)) {
            return $value;
        }
        
        $decimals = $options['decimals'] ?? 0;
        $decimalSeparator = $options['decimal_separator'] ?? '.';
        $thousandsSeparator = $options['thousands_separator'] ?? ',';
        
        return number_format($value, $decimals, $decimalSeparator, $thousandsSeparator);
    }
    
    public function getName(): string
    {
        return 'format_number';
    }
    
    public function getDescription(): string
    {
        return 'Format number with separators (e.g., 1234567 → "1,234,567")';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'decimals',
                'type' => 'number',
                'label' => 'Decimal Places',
                'default' => 0,
            ],
            [
                'name' => 'decimal_separator',
                'type' => 'text',
                'label' => 'Decimal Separator',
                'default' => '.',
            ],
            [
                'name' => 'thousands_separator',
                'type' => 'text',
                'label' => 'Thousands Separator',
                'default' => ',',
            ],
        ];
    }
}
