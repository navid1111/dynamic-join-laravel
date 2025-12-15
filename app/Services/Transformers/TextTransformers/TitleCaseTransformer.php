<?php

namespace App\Services\Transformers\TextTransformers;

use App\Services\Transformers\ColumnTransformerInterface;
use Illuminate\Support\Str;

class TitleCaseTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return $value;
        }
        
        return Str::title($value);
    }
    
    public function getName(): string
    {
        return 'title_case';
    }
    
    public function getDescription(): string
    {
        return 'Convert Text To Title Case';
    }
    
    public function getConfigSchema(): array
    {
        return [];
    }
}
