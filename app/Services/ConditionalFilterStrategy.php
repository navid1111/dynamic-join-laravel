<?php

// app/Services/ConditionalFilterStrategy.php

namespace App\Services;

class ConditionalFilterStrategy implements FilterStrategyInterface
{
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
        switch ($type) {
            case 'date':
                $bindings[] = $value;

                return "DATE({$table}.{$column}) = ?";

            case 'date_range':
                return $this->buildDateRangeClause($table, $column, $value, $bindings);

            case 'number_range':
                return $this->buildNumberRangeClause($table, $column, $value, $bindings);

            case 'text':
                $bindings[] = "%{$value}%";

                return "{$table}.{$column} LIKE ?";

            default:
                $bindings[] = $value;

                return "{$table}.{$column} = ?";
        }
    }

    private function buildDateRangeClause(string $table, string $column, array $value, array &$bindings): ?string
    {
        $clauses = [];

        if (! empty($value['min'])) {
            $bindings[] = $value['min'];
            $clauses[] = "DATE({$table}.{$column}) >= ?";
        }

        if (! empty($value['max'])) {
            $bindings[] = $value['max'];
            $clauses[] = "DATE({$table}.{$column}) <= ?";
        }

        return ! empty($clauses) ? '('.implode(' AND ', $clauses).')' : null;
    }

    private function buildNumberRangeClause(string $table, string $column, array $value, array &$bindings): ?string
    {
        $clauses = [];

        if (isset($value['min']) && $value['min'] !== '') {
            $bindings[] = $value['min'];
            $clauses[] = "{$table}.{$column} >= ?";
        }

        if (isset($value['max']) && $value['max'] !== '') {
            $bindings[] = $value['max'];
            $clauses[] = "{$table}.{$column} <= ?";
        }

        return ! empty($clauses) ? '('.implode(' AND ', $clauses).')' : null;
    }
}
