<?php

namespace App\Services\Transformers\NumberTransformers;

use App\Services\Transformers\ColumnTransformerInterface;

class FormatCurrencyTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (!is_numeric($value)) {
            return $value;
        }
        
        $currency = $options['currency'] ?? 'USD';
        $locale = $options['locale'] ?? 'en_US';
        $decimals = $options['decimals'] ?? 2;
        
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
        
        return $formatter->formatCurrency($value, $currency);
    }
    
    public function getName(): string
    {
        return 'format_currency';
    }
    
    public function getDescription(): string
    {
        return 'Format number as currency (e.g., 1234.56 → "$1,234.56")';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'currency',
                'type' => 'select',
                'label' => 'Currency',
                'options' => [
                    'USD' => 'US Dollar ($)',
                    'EUR' => 'Euro (€)',
                    'GBP' => 'British Pound (£)',
                    'JPY' => 'Japanese Yen (¥)',
                    'BDT' => 'Bangladeshi Taka (৳)',
                ],
                'default' => 'USD',
            ],
            [
                'name' => 'decimals',
                'type' => 'number',
                'label' => 'Decimal Places',
                'default' => 2,
            ],
        ];
    }
}
