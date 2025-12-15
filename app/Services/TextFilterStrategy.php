<?php

// app/Services/TextFilterStrategy.php

namespace App\Services;

class TextFilterStrategy implements FilterStrategyInterface
{
    public function buildWhereClause(array $filter, $value, array &$bindings): ?string
    {
        $table = $filter['table'];
        $column = $filter['column'];

        $bindings[] = "%{$value}%";

        return "{$table}.{$column} LIKE ?";
    }
}
