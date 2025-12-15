<?php

namespace App\Services\Transformers\TextTransformers;

use App\Services\Transformers\ColumnTransformerInterface;
use Illuminate\Support\Str;

class TruncateTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return $value;
        }
        
        $length = $options['length'] ?? 50;
        $end = $options['end'] ?? '...';
        
        return Str::limit($value, $length, $end);
    }
    
    public function getName(): string
    {
        return 'truncate';
    }
    
    public function getDescription(): string
    {
        return 'Truncate text to specified length';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'length',
                'type' => 'number',
                'label' => 'Maximum Length',
                'default' => 50,
                'required' => true,
            ],
            [
                'name' => 'end',
                'type' => 'text',
                'label' => 'Ending String',
                'default' => '...',
                'required' => false,
            ],
        ];
    }
}
