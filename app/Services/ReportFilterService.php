<?php

// app/Services/ReportFilterService.php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ReportFilterService
{
    /**
     * Apply filters to a base SQL query
     *
     * @param  string  $baseSql  The generated SQL from ReportQueryBuilder
     * @param  array  $filterDefinitions  Filter config from report
     * @param  array  $filterValues  User-submitted filter values
     * @return array ['sql' => string, 'bindings' => array]
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
            if (! isset($filterValues[$filterKey])) {
                continue;
            }

            $value = $filterValues[$filterKey];

            // Skip empty non-required filters
            if (empty($value) && ! ($filter['required'] ?? false)) {
                continue;
            }

            $whereClause = $this->buildWhereClause($filter, $value, $bindings);

            if ($whereClause) {
                $whereClauses[] = $whereClause;
            }
        }

        // Append WHERE clauses to base SQL
        if (! empty($whereClauses)) {
            // Check if SQL already contains a WHERE clause
            if (stripos($baseSql, 'WHERE') !== false) {
                $baseSql .= ' AND '.implode(' AND ', $whereClauses);
            } else {
                $baseSql .= ' WHERE '.implode(' AND ', $whereClauses);
            }
        }

        return [
            'sql' => $baseSql,
            'bindings' => $bindings,
        ];
    }

    /**
     * Build WHERE clause based on filter type
     */
    private function buildWhereClause(array $filter, $value, array &$bindings): ?string
    {
        $table = $filter['table'];
        $column = $filter['column'];
        $type = $filter['type'];

        switch ($type) {
            case 'dropdown':
            case 'select':
                return $this->buildEqualsClause($table, $column, $value, $bindings);

            case 'text':
                return $this->buildLikeClause($table, $column, $value, $bindings);

            case 'date_range':
                return $this->buildDateRangeClause($table, $column, $value, $bindings);

            case 'checkbox':
                return $this->buildInClause($table, $column, $value, $bindings);

            case 'number_range':
                return $this->buildNumberRangeClause($table, $column, $value, $bindings);

            default:
                return null;
        }
    }

    /**
     * Build simple equality clause (e.g., status = 'active')
     */
    private function buildEqualsClause(string $table, string $column, $value, array &$bindings): string
    {
        $bindings[] = $value;

        return "{$table}.{$column} = ?";
    }

    /**
     * Build LIKE clause for text search
     */
    private function buildLikeClause(string $table, string $column, string $value, array &$bindings): string
    {
        $bindings[] = "%{$value}%";

        return "{$table}.{$column} LIKE ?";
    }

    /**
     * Build date range clause
     * Expected format: ['start' => '2024-01-01', 'end' => '2024-12-31']
     */
    private function buildDateRangeClause(string $table, string $column, array $value, array &$bindings): ?string
    {
        $clauses = [];

        if (! empty($value['start'])) {
            $bindings[] = $value['start'];
            $clauses[] = "{$table}.{$column} >= ?";
        }

        if (! empty($value['end'])) {
            $bindings[] = $value['end'];
            $clauses[] = "{$table}.{$column} <= ?";
        }

        return ! empty($clauses) ? '('.implode(' AND ', $clauses).')' : null;
    }

    /**
     * Build IN clause for multiple selections
     * Expected format: ['value1', 'value2', 'value3']
     */
    private function buildInClause(string $table, string $column, array $values, array &$bindings): ?string
    {
        if (empty($values)) {
            return null;
        }

        $placeholders = [];
        foreach ($values as $value) {
            $bindings[] = $value;
            $placeholders[] = '?';
        }

        return "{$table}.{$column} IN (".implode(', ', $placeholders).')';
    }

    /**
     * Build number range clause
     * Expected format: ['min' => 100, 'max' => 1000]
     */
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

    /**
     * Get available options for a filter
     * Useful for populating dropdowns dynamically
     */
    public function getFilterOptions(array $filter): array
    {
        $optionsSource = $filter['options_source'] ?? 'static';

        if ($optionsSource === 'static') {
            return $filter['options'] ?? [];
        }

        if ($optionsSource === 'query' && ! empty($filter['options_query'])) {
            // Execute query to get dynamic options
            $results = DB::select($filter['options_query']);

            return collect($results)->pluck('name', 'id')->toArray();
        }

        return [];
    }
}
