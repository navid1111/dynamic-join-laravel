<?php

namespace App\Services\Transformers\TextTransformers;

use App\Services\Transformers\ColumnTransformerInterface;
use Illuminate\Support\Str;

class MaskTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return $value;
        }
        
        $character = $options['character'] ?? '*';
        $start = $options['start'] ?? 0;
        $length = $options['length'] ?? null;
        
        return Str::mask($value, $character, $start, $length);
    }
    
    public function getName(): string
    {
        return 'mask';
    }
    
    public function getDescription(): string
    {
        return 'Mask sensitive data (e.g., "john@email.com" → "j***@email.com")';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'character',
                'type' => 'text',
                'label' => 'Mask Character',
                'default' => '*',
                'required' => false,
            ],
            [
                'name' => 'start',
                'type' => 'number',
                'label' => 'Start Position',
                'default' => 0,
                'required' => false,
            ],
            [
                'name' => 'length',
                'type' => 'number',
                'label' => 'Length to Mask (null = all)',
                'default' => null,
                'required' => false,
            ],
        ];
    }
}
