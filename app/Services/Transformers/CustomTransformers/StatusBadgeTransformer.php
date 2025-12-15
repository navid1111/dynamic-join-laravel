<?php

namespace App\Services\Transformers\CustomTransformers;

use App\Services\Transformers\ColumnTransformerInterface;

class StatusBadgeTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        if (empty($value)) {
            return $value;
        }
        
        $colorMap = $options['color_map'] ?? [
            'active' => 'success',
            'pending' => 'warning',
            'inactive' => 'secondary',
            'cancelled' => 'danger',
        ];
        
        $color = $colorMap[strtolower($value)] ?? 'primary';
        
        return "<span class='badge bg-{$color}'>" . ucfirst($value) . "</span>";
    }
    
    public function getName(): string
    {
        return 'status_badge';
    }
    
    public function getDescription(): string
    {
        return 'Display status as colored badge';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'color_map',
                'type' => 'key_value',
                'label' => 'Status → Color Mapping',
                'default' => [
                    'active' => 'success',
                    'pending' => 'warning',
                    'inactive' => 'secondary',
                ],
            ],
        ];
    }
}
