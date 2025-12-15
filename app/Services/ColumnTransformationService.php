<?php

namespace App\Services;

use App\Services\Transformers\TransformerFactory;

class ColumnTransformationService
{
    private TransformerFactory $transformerFactory;
    
    public function __construct(TransformerFactory $transformerFactory)
    {
        $this->transformerFactory = $transformerFactory;
    }
    
    /**
     * Apply transformations to report data
     * 
     * @param array $data Raw data from database
     * @param array $transformations Column transformation config
     * @return array Transformed data
     */
    public function applyTransformations(array $data, array $transformations): array
    {
        if (empty($data) || empty($transformations)) {
            return $data;
        }
        
        return array_map(function ($row) use ($transformations) {
            return $this->transformRow($row, $transformations);
        }, $data);
    }
    
    /**
     * Transform a single row
     */
    private function transformRow($row, array $transformations): object|array
    {
        $isObject = is_object($row);
        $rowArray = $isObject ? (array) $row : $row;
        
        foreach ($transformations as $columnKey => $config) {
            // columnKey format: "table_name.column_name" or "column_name"
            $actualKey = $this->findActualColumnKey($rowArray, $columnKey);
            
            if ($actualKey === null || !isset($rowArray[$actualKey])) {
                continue;
            }
            
            $originalValue = $rowArray[$actualKey];
            
            // Apply each transformer in the chain
            $transformedValue = $this->applyTransformerChain(
                $originalValue, 
                $config['transformers'] ?? []
            );
            
            $rowArray[$actualKey] = $transformedValue;
        }
        
        return $isObject ? (object) $rowArray : $rowArray;
    }
    
    /**
     * Apply a chain of transformers to a value
     */
    private function applyTransformerChain($value, array $transformers)
    {
        foreach ($transformers as $transformerConfig) {
            $transformerName = $transformerConfig['name'];
            $options = $transformerConfig['options'] ?? [];
            
            $value = $this->transformerFactory->transform(
                $transformerName,
                $value,
                $options
            );
        }
        
        return $value;
    }
    
    /**
     * Find actual column key in row (handles aliased columns)
     */
    private function findActualColumnKey(array $row, string $searchKey): ?string
    {
        // Try exact match first
        if (array_key_exists($searchKey, $row)) {
            return $searchKey;
        }
        
        // Try with dot replaced by underscore (table.col -> table_col)
        $underscoreKey = str_replace('.', '_', $searchKey);
        if (array_key_exists($underscoreKey, $row)) {
            return $underscoreKey;
        }

        // Try with table prefix (table_column format)
        // If searchKey is already table.column, we checked table_column above. 
        // If searchKey is just 'column', we check for *_column
        foreach (array_keys($row) as $key) {
            if (str_ends_with($key, "_{$searchKey}") || $key === $searchKey) {
                return $key;
            }
        }
        
        // Try simple column name (if searchKey key is table.column)
        if (str_contains($searchKey, '.')) {
            $parts = explode('.', $searchKey);
            $columnName = end($parts);
            if (array_key_exists($columnName, $row)) {
                return $columnName;
            }
        }
        
        return null;
    }
    
    /**
     * Get original (untransformed) value for a column
     * Useful for filters and exports
     */
    public function getOriginalValue($row, string $columnKey)
    {
        $isObject = is_object($row);
        $rowArray = $isObject ? (array) $row : $row;
        
        $actualKey = $this->findActualColumnKey($rowArray, $columnKey);
        
        return $actualKey ? $rowArray[$actualKey] : null;
    }
}
