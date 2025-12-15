<?php

namespace App\Services\Transformers;

interface ColumnTransformerInterface
{
    /**
     * Transform a column value for display
     * 
     * @param mixed $value The raw value from database
     * @param array $options Transformer-specific options
     * @return mixed The transformed value
     */
    public function transform($value, array $options = []);
    
    /**
     * Get transformer name/identifier
     */
    public function getName(): string;
    
    /**
     * Get human-readable description
     */
    public function getDescription(): string;
    
    /**
     * Get configuration schema for this transformer
     * Returns array of option definitions
     */
    public function getConfigSchema(): array;
}
