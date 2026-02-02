<?php

// app/Services/ConditionalFilterStrategy.php

namespace App\Services;

class ConditionalFilterStrategy implements FilterStrategyInterface
{
    private DateRangeFilterStrategy $dateRangeStrategy;

    private NumberRangeFilterStrategy $numberRangeStrategy;

    private TextFilterStrategy $textStrategy;

    private SimpleFilterStrategy $simpleStrategy;

    public function __construct()
    {
        $this->dateRangeStrategy = new DateRangeFilterStrategy;
        $this->numberRangeStrategy = new NumberRangeFilterStrategy;
        $this->textStrategy = new TextFilterStrategy;
        $this->simpleStrategy = new SimpleFilterStrategy;
    }

    public function buildWhereClause(array $filter, $value, array &$bindings): ?string
    {
        // Value format: ['target' => 'booking_date', 'value' => '2024-01-01']
        if (! is_array($value) || ! isset($value['target']) || ! isset($value['value'])) {
            return null;
        }

        $selectedTarget = $value['target'];
        $filterValue = $value['value'];

        // Find the selected target configuration
        $targetConfig = collect($filter['conditional_targets'] ?? [])
            ->firstWhere('key', $selectedTarget);

        if (! $targetConfig) {
            return null;
        }

        $table = $targetConfig['table'];
        $column = $targetConfig['column'];
        $baseType = $filter['type']; // date, date_range, number_range, etc.

        // Delegate to appropriate strategy based on base type
        return $this->buildClauseForBaseType($baseType, $table, $column, $filterValue, $bindings);
    }

    private function buildClauseForBaseType(string $type, string $table, string $column, $value, array &$bindings): ?string
    {
        // Create a temporary filter array for delegation
        $tempFilter = ['table' => $table, 'column' => $column];

        switch ($type) {
            case 'date':
                $bindings[] = $value;

                return "DATE({$table}.{$column}) = ?";

            case 'date_range':
                return $this->dateRangeStrategy->buildWhereClause($tempFilter, $value, $bindings);

            case 'number_range':
                return $this->numberRangeStrategy->buildWhereClause($tempFilter, $value, $bindings);

            case 'text':
                return $this->textStrategy->buildWhereClause($tempFilter, $value, $bindings);

            default:
                return $this->simpleStrategy->buildWhereClause($tempFilter, $value, $bindings);
        }
    }
}
