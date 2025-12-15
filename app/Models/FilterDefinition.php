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
        'is_conditional',
        'conditional_targets',
        'conditional_type',
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
        'conditional_targets' => 'array',
        'required' => 'boolean',
        'is_active' => 'boolean',
        'is_conditional' => 'boolean',
        'conditional_type' => 'array',
    ];

    /**
     * Return options as key-value pairs
     * Return ['key' => 'value'] for static options
     */
    public function getOptionsArray(): array
    {
        if ($this->options_source === 'static' && is_array($this->options)) {
            return $this->options;
        }

        return [];
    }

    /**
     * Get option keys only (for filtering)
     */
    public function getOptionKeys(): array
    {
        return array_keys($this->getOptionsArray());
    }

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

        if($this->is_conditional){
            foreach($this->getConditionalTargets() as $target){
                if(in_array($target['table'], $reportTables)){
                    return true;
                }
            }
            return false;
        }

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

    public function getConditionalTargets(): array
    {
        if(!$this->is_conditional || empty($this->conditional_targets)){
            return [];
        }

        return $this->conditional_targets;
    }
}
