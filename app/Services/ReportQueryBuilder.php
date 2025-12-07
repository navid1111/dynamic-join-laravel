<?php

namespace App\Services;

class ReportQueryBuilder
{
    private array $tables = [];

    private array $joins = [];

    private array $columnAliases = [];

    private array $duplicateColumns = [];

    private array $tableAliasMap = [];

    /**
     * Build SQL query from report configuration
     */
    public function build(array $reportConfig): string
    {
        $this->reset();
        $this->parseConfiguration($reportConfig);

        return sprintf(
            'SELECT %s FROM %s',
            $this->buildSelectClause(),
            $this->buildJoinClause()
        );
    }

    /**
     * Reset builder state for fresh build
     */
    private function reset(): void
    {
        $this->tables = [];
        $this->joins = [];
        $this->columnAliases = [];
        $this->duplicateColumns = [];
        $this->tableAliasMap = [];
    }

    /**
     * Parse and store report configuration
     */
    private function parseConfiguration(array $config): void
    {
        $this->tables = $config['tables'] ?? [];
        $this->joins = $config['joins'] ?? [];
        $this->detectDuplicateColumns();
        $this->generateTableAliases();
    }

    /**
     * Build SELECT clause with proper column aliases
     * Matches original logic: only alias if column is in duplicate list AND hasn't been added yet
     */
    private function buildSelectClause(): string
    {
        $selectColumns = [];
        $addedColumns = []; // Track what we've already added per table

        foreach ($this->tables as $index => $tableGroup) {
            foreach ($tableGroup as $tableName => $columns) {
                $alias = $this->tableAliasMap[$index];

                // Initialize tracking for this table
                if (! isset($addedColumns[$tableName])) {
                    $addedColumns[$tableName] = [];
                }

                foreach ($columns as $column) {
                    // Skip if we've already added this column for this table
                    if (in_array($column, $addedColumns[$tableName])) {
                        continue;
                    }

                    // Add to tracking
                    $addedColumns[$tableName][] = $column;

                    // Add column with alias if it's a duplicate across tables
                    if (in_array($column, $this->duplicateColumns)) {
                        $selectColumns[] = "{$alias}.{$column} as {$tableName}_{$column}";
                    } else {
                        $selectColumns[] = "{$tableName}.{$column}";
                    }
                }
            }
        }

        return implode(', ', $selectColumns);
    }

    /**
     * Build JOIN clause with proper table aliases
     */
    private function buildJoinClause(): string
    {
        $firstTable = $this->getFirstTableName();
        $firstAlias = $this->tableAliasMap[0];

        $joinClause = "{$firstTable} {$firstAlias}";

        foreach ($this->joins as $index => $join) {
            $tableIndex = $index + 1;
            $rightAlias = $this->tableAliasMap[$tableIndex];
            $rightTable = $this->getTableNameByIndex($tableIndex);

            $joinClause .= " {$join['join_type']} JOIN {$rightTable} {$rightAlias}";

            // Add ON condition for non-cross joins
            if (strtolower($join['join_type']) !== 'cross') {
                $joinClause .= " ON {$join['left_table']}.{$join['left_column']} = {$join['right_table']}.{$join['right_column']}";
            }
        }

        return $joinClause;
    }

    /**
     * Detect columns that appear in multiple tables
     * Only marks as duplicate if found AFTER first occurrence
     */
    private function detectDuplicateColumns(): void
    {
        $encounteredColumns = [];
        $duplicates = [];

        foreach ($this->tables as $tableGroup) {
            foreach ($tableGroup as $tableName => $columns) {
                foreach ($columns as $column) {
                    if (in_array($column, $encounteredColumns)) {
                        // Only add to duplicates if not already there
                        if (! in_array($column, $duplicates)) {
                            $duplicates[] = $column;
                        }
                    } else {
                        $encounteredColumns[] = $column;
                    }
                }
            }
        }

        $this->duplicateColumns = $duplicates;
    }

    /**
     * Generate unique aliases for tables (handling same table used multiple times)
     * Matches original logic: concatenate table name N times where N is occurrence count
     */
    private function generateTableAliases(): void
    {
        $tableOccurrences = [];

        foreach ($this->tables as $index => $tableGroup) {
            foreach ($tableGroup as $tableName => $columns) {
                // Initialize or increment count
                if (! isset($tableOccurrences[$tableName])) {
                    $tableOccurrences[$tableName] = 1;
                } else {
                    $tableOccurrences[$tableName]++;
                }

                // Build alias by concatenating table name N times
                $alias = '';
                for ($i = 0; $i < $tableOccurrences[$tableName]; $i++) {
                    $alias .= $tableName;
                }

                $this->tableAliasMap[$index] = $alias;
            }
        }
    }

    /**
     * Get the first table name from configuration
     */
    private function getFirstTableName(): string
    {
        $firstTableGroup = reset($this->tables);

        return array_key_first($firstTableGroup);
    }

    /**
     * Get table name by its index in the tables array
     */
    private function getTableNameByIndex(int $index): string
    {
        $tableGroup = $this->tables[$index];

        return array_key_first($tableGroup);
    }
}
