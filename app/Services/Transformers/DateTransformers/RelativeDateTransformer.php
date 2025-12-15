<?php

namespace App\Services\Transformers\DateTransformers;

use App\Services\Transformers\ColumnTransformerInterface;
use Carbon\Carbon;

class RelativeDateTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return null;
        }
        
        try {
            return Carbon::parse($value)->diffForHumans();
            // "2 days ago", "3 months ago", "in 5 days"
        } catch (\Exception $e) {
            return $value;
        }
    }
    
    public function getName(): string
    {
        return 'relative_date';
    }
    
    public function getDescription(): string
    {
        return 'Show date relative to now (e.g., "2 days ago")';
    }
    
    public function getConfigSchema(): array
    {
        return [];
    }
}
