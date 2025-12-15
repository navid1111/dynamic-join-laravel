<?php

namespace App\Services\Transformers\TextTransformers;

use App\Services\Transformers\ColumnTransformerInterface;

class LowercaseTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return $value;
        }
        
        return mb_strtolower($value);
    }
    
    public function getName(): string
    {
        return 'lowercase';
    }
    
    public function getDescription(): string
    {
        return 'Convert text to lowercase';
    }
    
    public function getConfigSchema(): array
    {
        return [];
    }
}
