<?php
// app/Services/NumberRangeFilterStrategy.php

namespace App\Services;

class NumberRangeFilterStrategy implements FilterStrategyInterface
{
    public function buildWhereClause(array $filter, $value, array &$bindings): ?string
    {
        if (!is_array($value)) {
            return null;
        }

        $table = $filter['table'];
        $column = $filter['column'];
        $clauses = [];

        if (isset($value['min']) && $value['min'] !== '') {
            $bindings[] = $value['min'];
            $clauses[] = "{$table}.{$column} >= ?";
        }

        if (isset($value['max']) && $value['max'] !== '') {
            $bindings[] = $value['max'];
            $clauses[] = "{$table}.{$column} <= ?";
        }

        return !empty($clauses) ? '(' . implode(' AND ', $clauses) . ')' : null;
    }
}