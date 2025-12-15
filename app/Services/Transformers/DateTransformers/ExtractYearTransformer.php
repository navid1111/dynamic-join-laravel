<?php

namespace App\Services\Transformers\DateTransformers;

use App\Services\Transformers\ColumnTransformerInterface;
use Carbon\Carbon;

class ExtractYearTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return $options['empty_value'] ?? null;
        }
        
        try {
            return Carbon::parse($value)->year;
        } catch (\Exception $e) {
            return $value; // Return original if can't parse
        }
    }
    
    public function getName(): string
    {
        return 'extract_year';
    }
    
    public function getDescription(): string
    {
        return 'Extract year from date (e.g., "2024-03-15" → "2024")';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'empty_value',
                'type' => 'text',
                'label' => 'Value to show when date is empty',
                'default' => null,
                'required' => false,
            ],
        ];
    }
}
