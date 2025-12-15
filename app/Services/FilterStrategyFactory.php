<?php

// app/Services/FilterStrategyFactory.php

namespace App\Services;

class FilterStrategyFactory
{
    private array $strategies = [];

    public function __construct()
    {
        $this->registerDefaultStrategies();
    }

    private function registerDefaultStrategies(): void
    {
        $this->strategies['conditional'] = new ConditionalFilterStrategy;
        $this->strategies['dropdown'] = new SimpleFilterStrategy;
        $this->strategies['text'] = new TextFilterStrategy;
        $this->strategies['date_range'] = new DateRangeFilterStrategy;
        $this->strategies['number_range'] = new NumberRangeFilterStrategy;
        $this->strategies['checkbox'] = new MultiValueFilterStrategy;
        $this->strategies['multi_select'] = new MultiValueFilterStrategy;
        $this->strategies['radio'] = new MultiValueFilterStrategy;
    }

    public function getStrategy(array $filter): FilterStrategyInterface
    {
        // Check if it's a conditional filter
        if ($filter['is_conditional'] ?? false) {
            return $this->strategies['conditional'];
        }

        $type = $filter['type'];

        return $this->strategies[$type] ?? $this->strategies['dropdown'];
    }

    /**
     * Register custom strategy
     */
    public function register(string $type, FilterStrategyInterface $strategy): void
    {
        $this->strategies[$type] = $strategy;
    }
}
