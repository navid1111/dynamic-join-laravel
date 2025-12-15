<?php

namespace App\Services\Transformers;

use App\Services\Transformers\DateTransformers\ExtractYearTransformer;
use App\Services\Transformers\DateTransformers\ExtractMonthTransformer;
use App\Services\Transformers\DateTransformers\FormatDateTransformer;
use App\Services\Transformers\DateTransformers\RelativeDateTransformer;

use App\Services\Transformers\TextTransformers\UppercaseTransformer;
use App\Services\Transformers\TextTransformers\LowercaseTransformer;
use App\Services\Transformers\TextTransformers\TitleCaseTransformer;
use App\Services\Transformers\TextTransformers\TruncateTransformer;
use App\Services\Transformers\TextTransformers\MaskTransformer;

use App\Services\Transformers\NumberTransformers\FormatCurrencyTransformer;
use App\Services\Transformers\NumberTransformers\FormatNumberTransformer;
use App\Services\Transformers\NumberTransformers\PercentageTransformer;

use App\Services\Transformers\CustomTransformers\StatusBadgeTransformer;
use App\Services\Transformers\CustomTransformers\BooleanTransformer;

class TransformerFactory
{
    private array $transformers = [];
    
    public function __construct()
    {
        $this->registerDefaultTransformers();
    }
    
    private function registerDefaultTransformers(): void
    {
        // Date Transformers
        $this->register(new ExtractYearTransformer());
        $this->register(new ExtractMonthTransformer());
        $this->register(new FormatDateTransformer());
        $this->register(new RelativeDateTransformer());
        
        // Text Transformers
        $this->register(new UppercaseTransformer());
        $this->register(new LowercaseTransformer());
        $this->register(new TitleCaseTransformer());
        $this->register(new TruncateTransformer());
        $this->register(new MaskTransformer());
        
        // Number Transformers
        $this->register(new FormatCurrencyTransformer());
        $this->register(new FormatNumberTransformer());
        $this->register(new PercentageTransformer());
        
        // Custom Transformers
        $this->register(new StatusBadgeTransformer());
        $this->register(new BooleanTransformer());
    }
    
    /**
     * Register a transformer
     */
    public function register(ColumnTransformerInterface $transformer): void
    {
        $this->transformers[$transformer->getName()] = $transformer;
    }
    
    /**
     * Get transformer by name
     */
    public function getTransformer(string $name): ?ColumnTransformerInterface
    {
        return $this->transformers[$name] ?? null;
    }
    
    /**
     * Get all registered transformers
     */
    public function getAllTransformers(): array
    {
        return $this->transformers;
    }
    
    /**
     * Get transformers grouped by category
     */
    public function getTransformersByCategory(): array
    {
        $grouped = [
            'Date & Time' => [],
            'Text' => [],
            'Numbers' => [],
            'Custom' => [],
        ];
        
        foreach ($this->transformers as $name => $transformer) {
            $class = get_class($transformer);
            
            if (str_contains($class, 'DateTransformers')) {
                $grouped['Date & Time'][$name] = $transformer;
            } elseif (str_contains($class, 'TextTransformers')) {
                $grouped['Text'][$name] = $transformer;
            } elseif (str_contains($class, 'NumberTransformers')) {
                $grouped['Numbers'][$name] = $transformer;
            } else {
                $grouped['Custom'][$name] = $transformer;
            }
        }
        
        return $grouped;
    }
    
    /**
     * Transform a value using specified transformer
     */
    public function transform(string $transformerName, $value, array $options = [])
    {
        $transformer = $this->getTransformer($transformerName);
        
        if (!$transformer) {
            return $value; // Return original value if transformer not found
        }
        
        return $transformer->transform($value, $options);
    }

    /**
     * Get transformer type compatibility
     */
    public function getTypeCompatibility(): array
    {
        return [
            'Date & Time' => ['date', 'datetime', 'timestamp', 'time', 'year'],
            'Text' => ['varchar', 'char', 'text', 'string', 'enum', 'mediumtext', 'longtext'],
            'Numbers' => ['int', 'integer', 'tinyint', 'smallint', 'mediumint', 'bigint', 'decimal', 'float', 'double', 'real'],
            'Custom' => ['*'] // Available for all types
        ];
    }
}
