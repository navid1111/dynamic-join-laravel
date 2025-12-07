<?php

// app/Models/FilterDefinition.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
        'type',
        'target_table',
        'target_column',
        'options_source',
        'options',
        'options_query',
        'required',
        'placeholder',
        'description',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Reports using this filter
     */
    public function reports()
    {
        return $this->belongsToMany(Report::class, 'report_filters')
            ->withPivot('order', 'custom_label')
            ->withTimestamps()
            ->orderBy('report_filters.order');
    }

    /**
     * Check if this filter is compatible with a report's structure
     */
    public function isCompatibleWithReport(Report $report): bool
    {
        $reportTables = $this->extractTablesFromReport($report);

        return in_array($this->target_table, $reportTables);
    }

    /**
     * Extract all table names used in a report
     */
    private function extractTablesFromReport(Report $report): array
    {
        $tables = [];
        foreach ($report->report_details['tables'] ?? [] as $tableGroup) {
            $tables = array_merge($tables, array_keys($tableGroup));
        }

        return array_unique($tables);
    }
}
