<?php

namespace App\Services\Transformers\CustomTransformers;

use App\Services\Transformers\ColumnTransformerInterface;

class BooleanTransformer implements ColumnTransformerInterface
{
    public function transform($value, array $options = [])
    {
        $trueValue = $options['true_value'] ?? 'Yes';
        $falseValue = $options['false_value'] ?? 'No';
        $format = $options['format'] ?? 'text'; // 'text', 'icon', 'badge'
        
        $isTrue = in_array($value, [true, 1, '1', 'true', 'yes', 'on'], true);
        
        if ($format === 'icon') {
            return $isTrue 
                ? '<i class="fas fa-check-circle text-success"></i>' 
                : '<i class="fas fa-times-circle text-danger"></i>';
        }
        
        if ($format === 'badge') {
            $badgeClass = $isTrue ? 'success' : 'secondary';
            $text = $isTrue ? $trueValue : $falseValue;
            return "<span class='badge bg-{$badgeClass}'>{$text}</span>";
        }
        
        return $isTrue ? $trueValue : $falseValue;
    }
    
    public function getName(): string
    {
        return 'boolean';
    }
    
    public function getDescription(): string
    {
        return 'Format boolean/checkbox values';
    }
    
    public function getConfigSchema(): array
    {
        return [
            [
                'name' => 'format',
                'type' => 'select',
                'label' => 'Display Format',
                'options' => [
                    'text' => 'Text (Yes/No)',
                    'icon' => 'Icon (✓/✗)',
                    'badge' => 'Badge',
                ],
                'default' => 'text',
            ],
            [
                'name' => 'true_value',
                'type' => 'text',
                'label' => 'True Value Text',
                'default' => 'Yes',
            ],
            [
                'name' => 'false_value',
                'type' => 'text',
                'label' => 'False Value Text',
                'default' => 'No',
            ],
        ];
    }
}
