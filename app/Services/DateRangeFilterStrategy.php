<?php

// app/Services/DateRangeFilterStrategy.php

namespace App\Services;

class DateRangeFilterStrategy implements FilterStrategyInterface
{
    public function buildWhereClause(array $filter, $value, array &$bindings): ?string
    {
        if (! is_array($value)) {
            return null;
        }

        $table = $filter['table'];
        $column = $filter['column'];
        $clauses = [];

        if (! empty($value['start'])) {
            $bindings[] = $value['start'];
            $clauses[] = "DATE({$table}.{$column}) >= ?";
        }

        if (! empty($value['end'])) {
            $bindings[] = $value['end'];
            $clauses[] = "DATE({$table}.{$column}) <= ?";
        }

        return ! empty($clauses) ? '('.implode(' AND ', $clauses).')' : null;
    }
}
