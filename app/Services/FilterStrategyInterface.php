<?php

// app/Services/FilterStrategyInterface.php

namespace App\Services;

interface FilterStrategyInterface
{
    /**
     * Build WHERE clause for this filter type
     */
    public function buildWhereClause(array $filter, $value, array &$bindings): ?string;
}
