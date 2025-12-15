<?php

namespace App\Services\Transformers\TextTransformers;

use App\Services\Transformers\ColumnTransformerInterface;

class UppercaseTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return $value;
        }
        
        return mb_strtoupper($value);
    }
    
    public function getName(): string
    {
        return 'uppercase';
    }
    
    public function getDescription(): string
    {
        return 'Convert text to UPPERCASE';
    }
    
    public function getConfigSchema(): array
    {
        return [];
    }
}
