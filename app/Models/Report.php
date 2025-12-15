<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'report_details', 'filters', 'users', 'column_transformations'];

    protected $casts = [
        'report_details' => 'array',
        'column_transformations' => 'array',
        'filters' => 'array',
        'users' => 'array',
    ];

    public function getFilterDefinitions(): array
    {
        return $this->filters ?? [];
    }

    /**
     * Filters assigned to this report (NEW SYSTEM)
     */
    public function filterDefinitions()
    {
        return $this->belongsToMany(FilterDefinition::class, 'report_filters')
            ->withPivot('order', 'custom_label')
            ->withTimestamps()
            ->orderBy('report_filters.order');
    }

    /**
     * Get active filters for this report (NEW SYSTEM)
     */
    public function getActiveFiltersNew()
    {
        return $this->filterDefinitions()->where('is_active', true)->get();
    }

    /**
     * Get all compatible filters that can be added to this report (NEW SYSTEM)
     */
    public function getCompatibleFilters()
    {
        $reportTables = $this->getUsedTables();

        return FilterDefinition::where('is_active', true)
            ->whereIn('target_table', $reportTables)
            ->whereNotIn('id', $this->filterDefinitions()->pluck('filter_definitions.id'))
            ->get();
    }

    /**
     * Extract all table names used in this report (NEW SYSTEM)
     */
    public function getUsedTables(): array
    {
        $tables = [];
        foreach ($this->report_details['tables'] ?? [] as $tableGroup) {
            $tables = array_merge($tables, array_keys($tableGroup));
        }

        return array_unique($tables);
    }
    /**
     * Get column transformations configuration
     */
    public function getColumnTransformations(): array
    {
        return $this->column_transformations ?? [];
    }
    
    /**
     * Check if a column has transformations
     */
    public function hasTransformation(string $columnKey): bool
    {
        $transformations = $this->getColumnTransformations();
        return isset($transformations[$columnKey]);
    }
    
    /**
     * Get all columns used in this report
     */
    public function getAllColumns(): array
    {
        $columns = [];
        foreach ($this->report_details['tables'] ?? [] as $tableGroup) {
            foreach ($tableGroup as $table => $cols) {
                foreach ($cols as $col) {
                    $columns[] = [
                        'table' => $table,
                        'column' => $col,
                        'key' => "{$table}.{$col}",
                        'display' => "{$table}.{$col}",
                    ];
                }
            }
        }
        return $columns;
    }
}
