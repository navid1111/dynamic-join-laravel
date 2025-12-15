<?php

// app/Services/Filters/SimpleFilterStrategy.php

namespace App\Services;

class SimpleFilterStrategy implements FilterStrategyInterface
{
    public function buildWhereClause(array $filter, $value, array &$bindings): ?string
    {
        $table = $filter['table'];
        $column = $filter['column'];

        $bindings[] = $value;

        return "{$table}.{$column} = ?";
    }
}
