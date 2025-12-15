<?php

namespace App\Services\Transformers\DateTransformers;

use App\Services\Transformers\ColumnTransformerInterface;
use Carbon\Carbon;

class ExtractMonthTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            $date = Carbon::parse($value);
            $format = $options['format'] ?? 'name'; // 'name', 'short', 'number'
            
            return match($format) {
                'name' => $date->format('F'),        // "January"
                'short' => $date->format('M'),       // "Jan"
                'number' => $date->format('m'),      // "01"
                default => $date->format('F'),
            };
        } catch (\Exception $e) {
            return $value;
        }
    }
    
    public function getName(): string
    {
        return 'extract_month';
    }
    
    public function getDescription(): string
    {
        return 'Extract month from date';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'format',
                'type' => 'select',
                'label' => 'Month Format',
                'options' => [
                    'name' => 'Full Name (January)',
                    'short' => 'Short Name (Jan)',
                    'number' => 'Number (01)',
                ],
                'default' => 'name',
            ],
        ];
    }
}
