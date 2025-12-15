<?php

namespace App\Services\Transformers\DateTransformers;

use App\Services\Transformers\ColumnTransformerInterface;
use Carbon\Carbon;

class FormatDateTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            $date = Carbon::parse($value);
            $format = $options['format'] ?? 'Y-m-d';
            
            return $date->format($format);
        } catch (\Exception $e) {
            return $value;
        }
    }
    
    public function getName(): string
    {
        return 'format_date';
    }
    
    public function getDescription(): string
    {
        return 'Format date with custom pattern';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'format',
                'type' => 'select',
                'label' => 'Date Format',
                'options' => [
                    'Y-m-d' => '2024-03-15',
                    'd/m/Y' => '15/03/2024',
                    'm/d/Y' => '03/15/2024',
                    'F d, Y' => 'March 15, 2024',
                    'M d, Y' => 'Mar 15, 2024',
                    'l, F d, Y' => 'Friday, March 15, 2024',
                ],
                'default' => 'Y-m-d',
            ],
        ];
    }
}
