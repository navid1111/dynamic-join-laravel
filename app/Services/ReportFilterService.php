<?php
// app/Services/ReportFilterService.php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportFilterService
{
    private FilterStrategyFactory $strategyFactory;

    public function __construct(FilterStrategyFactory $strategyFactory)
    {
        $this->strategyFactory = $strategyFactory;
    }

    /**
     * Apply filters to a base SQL query
     */
    public function applyFilters(string $baseSql, array $filterDefinitions, array $filterValues): array
    {
        if (empty($filterDefinitions) || empty($filterValues)) {
            return ['sql' => $baseSql, 'bindings' => []];
        }

        $whereClauses = [];
        $bindings = [];

        foreach ($filterDefinitions as $filter) {
            $filterKey = $filter['id'];

            // Skip if no value provided for this filter
            if (!isset($filterValues[$filterKey])) {
                continue;
            }

            $value = $filterValues[$filterKey];

            // Skip empty non-required filters
            if (empty($value) && !($filter['required'] ?? false)) {
                continue;
            }

            // Use strategy pattern to build WHERE clause
            $strategy = $this->strategyFactory->getStrategy($filter);
            $whereClause = $strategy->buildWhereClause($filter, $value, $bindings);

            if ($whereClause) {
                $whereClauses[] = $whereClause;
            }
        }

        // Append WHERE clauses to base SQL
        if (!empty($whereClauses)) {
            if (stripos($baseSql, 'WHERE') !== false) {
                $baseSql .= ' AND ' . implode(' AND ', $whereClauses);
            } else {
                $baseSql .= ' WHERE ' . implode(' AND ', $whereClauses);
            }
        }

        return [
            'sql' => $baseSql,
            'bindings' => $bindings,
        ];
    }

    /**
     * Get available options for a filter
     */
    public function getFilterOptions(array $filter): array
    {
        $optionsSource = $filter['options_source'] ?? 'static';

        if ($optionsSource === 'static') {
            return $filter['options'] ?? [];
        }

        if ($optionsSource === 'query' && !empty($filter['options_query'])) {
            $results = DB::select($filter['options_query']);
            return collect($results)->pluck('name', 'id')->toArray();
        }

        return [];
    }
}