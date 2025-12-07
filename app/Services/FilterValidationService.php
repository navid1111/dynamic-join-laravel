<?php
// app/Services/FilterValidationService.php

namespace App\Services;

use App\Models\FilterDefinition;
use App\Models\Report;

class FilterValidationService
{
    /**
     * Validate if a filter can be attached to a report
     */
    public function canAttachFilter(Report $report, FilterDefinition $filter): array
    {
        $errors = [];

        // Check if filter's target table exists in report
        $reportTables = $report->getUsedTables();
        if (!in_array($filter->target_table, $reportTables)) {
            $errors[] = "Filter targets table '{$filter->target_table}' which is not used in this report.";
        }

        // Check if filter's target column exists in report's table structure
        $reportColumns = $this->getColumnsForTable($report, $filter->target_table);
        if (!empty($reportColumns) && !in_array($filter->target_column, $reportColumns)) {
            $errors[] = "Filter targets column '{$filter->target_column}' which is not selected in this report.";
        }

        // Check if filter is already attached
        if ($report->filters()->where('filter_definition_id', $filter->id)->exists()) {
            $errors[] = "This filter is already attached to this report.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get all columns selected for a specific table in the report
     */
    private function getColumnsForTable(Report $report, string $tableName): array
    {
        $columns = [];
        foreach ($report->report_details['tables'] ?? [] as $tableGroup) {
            if (isset($tableGroup[$tableName])) {
                $columns = array_merge($columns, $tableGroup[$tableName]);
            }
        }
        return array_unique($columns);
    }
}