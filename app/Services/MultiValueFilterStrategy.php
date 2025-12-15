<?php

// app/Services/MultiValueFilterStrategy.php

namespace App\Services;

class MultiValueFilterStrategy implements FilterStrategyInterface
{
    public function buildWhereClause(array $filter, $value, array &$bindings): ?string
    {
        if (! is_array($value) || empty($value)) {
            return null;
        }

        $table = $filter['table'];
        $column = $filter['column'];

        $placeholders = [];
        foreach ($value as $val) {
            $bindings[] = $val;
            $placeholders[] = '?';
        }

        return "{$table}.{$column} IN (".implode(', ', $placeholders).')';
    }
}
