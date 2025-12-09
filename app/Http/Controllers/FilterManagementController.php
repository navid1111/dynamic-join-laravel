<?php

namespace App\Http\Controllers;

use App\Models\FilterDefinition;
use App\Models\Report;
use Illuminate\Http\Request;

class FilterManagementController extends Controller
{
    /**
     * Display a listing of filter definitions.
     */
    public function index()
    {
        $filters = FilterDefinition::orderBy('name')
            ->withCount('reports')
            ->paginate(20);

        return view('filters.index', compact('filters'));
    }

    /**
     * Show the form for creating a new filter definition.
     */
    public function create()
    {
        $tables = $this->getAvailableTables();
        $filterTypes = [
            'dropdown' => 'Dropdown',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
            'text' => 'Text Input',
            'number' => 'Number',
            'number_range' => 'Number Range',
            'date' => 'Date',
            'date_range' => 'Date Range',
            'multi_select' => 'Multi-Select Dropdown',
            'autocomplete' => 'Autocomplete',
        ];

        // Types that require options
        $typesWithOptions = ['dropdown', 'checkbox', 'radio', 'multi_select'];

        return view('filters.create', compact('tables', 'filterTypes', 'typesWithOptions'));
    }

    /**
     * Store a newly created filter definition in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:filter_definitions|max:100',
            'label' => 'required|string|max:100',
            'type' => 'required|in:dropdown,checkbox,radio,text,number,number_range,date,date_range,multi_select,autocomplete',
            'target_table' => 'required|string|max:100',
            'target_column' => 'required|string|max:100',
            'options_source' => 'nullable|in:static,dynamic',
            'option_keys' => 'nullable|array',        // NEW
            'option_keys.*' => 'nullable|string',     // NEW
            'option_values' => 'nullable|array',      // NEW
            'option_values.*' => 'nullable|string',   // NEW
            'options_table' => 'nullable|string|max:100',
            'options_column' => 'nullable|string|max:100',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $typesWithOptions = ['dropdown', 'checkbox', 'radio', 'multi_select'];

        if (in_array($validated['type'], $typesWithOptions)) {
            $validated['options_source'] = $request->input('options_source', 'dynamic');

            if ($validated['options_source'] === 'static') {
                // Build key-value object from separate arrays
                $keys = $request->input('option_keys', []);
                $values = $request->input('option_values', []);

                $options = [];
                foreach ($keys as $index => $key) {
                    if (! empty($key) && ! empty($values[$index])) {
                        $options[$key] = $values[$index];
                    }
                }

                $validated['options'] = $options; // Store as {"key": "value"}
                $validated['options_query'] = null;

            } elseif ($validated['options_source'] === 'dynamic') {
                $validated['options'] = null;
                $validated['options_query'] = "SELECT DISTINCT {$validated['options_column']} as id, {$validated['options_column']} as name FROM {$validated['options_table']} ORDER BY {$validated['options_column']}";
            }

            $validated['options_table'] = $request->input('options_table');
            $validated['options_column'] = $request->input('options_column');
        } else {
            $validated['options_source'] = 'none';
            $validated['options'] = null;
            $validated['options_query'] = null;
        }

        // Remove helper fields before creating
        unset($validated['option_keys']);
        unset($validated['option_values']);

        FilterDefinition::create($validated);

        return redirect()->route('filters.index')
            ->with('success', 'Filter created successfully.');
    }

    /**
     * Show the form for editing the specified filter definition.
     */
    public function edit(FilterDefinition $filterDefinition)
    {
        $tables = $this->getAvailableTables();
        $filterTypes = [
            'dropdown' => 'Dropdown',
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Button',
            'text' => 'Text Input',
            'number' => 'Number',
            'number_range' => 'Number Range',
            'date' => 'Date',
            'date_range' => 'Date Range',
            'multi_select' => 'Multi-Select Dropdown',
            'autocomplete' => 'Autocomplete',
        ];

        // Types that require options
        $typesWithOptions = ['dropdown', 'checkbox', 'radio', 'multi_select'];

        // Get columns for the target table
        $targetColumns = [];
        if ($filterDefinition->target_table) {
            try {
                $targetColumns = \Illuminate\Support\Facades\Schema::getColumnListing($filterDefinition->target_table);
            } catch (\Exception $e) {
                $targetColumns = [];
            }
        }

        return view('filters.edit', compact('filterDefinition', 'tables', 'filterTypes', 'typesWithOptions', 'targetColumns'));
    }

    /**
     * Update the specified filter definition in storage.
     */
    public function update(Request $request, FilterDefinition $filterDefinition)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:filter_definitions,name,'.$filterDefinition->id.'|max:100',
            'label' => 'required|string|max:100',
            'type' => 'required|in:dropdown,checkbox,radio,text,number,number_range,date,date_range,multi_select,autocomplete',
            'target_table' => 'required|string|max:100',
            'target_column' => 'required|string|max:100',
            'options_source' => 'nullable|in:static,dynamic',
            'options_table' => 'nullable|string|max:100',
            'options_column' => 'nullable|string|max:100',
            'required' => 'boolean',
            'placeholder' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Determine if this type needs options
        $typesWithOptions = ['dropdown', 'checkbox', 'radio', 'multi_select'];

        if (in_array($validated['type'], $typesWithOptions)) {
            // For types with options, validate options source
            $validated['options_source'] = $request->input('options_source', 'dynamic');

            if ($validated['options_source'] === 'static') {
                // Handle static options as key-value pairs
                $keys = $request->input('option_keys', []);
                $values = $request->input('option_values', []);
                $options = [];

                foreach ($keys as $index => $key) {
                    if (!empty($key) && isset($values[$index])) {
                        $options[$key] = $values[$index];
                    }
                }

                $validated['options'] = $options; // Store as {"key": "value"}
                $validated['options_query'] = null;
            } elseif ($validated['options_source'] === 'dynamic') {
                $validated['options'] = null;
                $validated['options_query'] = "SELECT DISTINCT {$validated['options_column']} as id, {$validated['options_column']} as name FROM {$validated['options_table']} ORDER BY {$validated['options_column']}";
            }
            $validated['options_table'] = $request->input('options_table');
            $validated['options_column'] = $request->input('options_column');
        } else {
            // For types without options, don't set them
            $validated['options_source'] = 'none';
            $validated['options'] = null;
            $validated['options_query'] = null;
        }

        $filterDefinition->update($validated);

        return redirect()->route('filters.index')
            ->with('success', 'Filter updated successfully.');
    }

    /**
     * Remove the specified filter definition from storage.
     */
    public function destroy(FilterDefinition $filterDefinition)
    {
        // Delete filter assignments first (pivot records)
        if ($filterDefinition->reports()->count() > 0) {
            $filterDefinition->reports()->detach();
        }

        // Then delete the filter itself
        $filterDefinition->delete();

        return redirect()->route('filters.index')
            ->with('success', 'Filter deleted successfully (removed from all reports).');
    }

    /**
     * Show reports this filter is assigned to
     */
    public function show(FilterDefinition $filterDefinition)
    {
        return view('filters.show', compact('filterDefinition'));
    }

    /**
     * Assign filter to reports
     */
    public function assignToReports(FilterDefinition $filterDefinition)
    {
        $allReports = Report::all();
        $assignedReports = $filterDefinition->reports()->pluck('reports.id')->toArray();

        // Filter reports to only those with compatible tables/columns
        $compatibleReports = [];
        foreach ($allReports as $report) {
            if ($filterDefinition->isCompatibleWithReport($report)) {
                $compatibleReports[] = [
                    'id' => $report->id,
                    'name' => $report->name,
                    'table' => $report->report_details['table'] ?? 'Unknown',
                    'is_assigned' => in_array($report->id, $assignedReports),
                ];
            }
        }

        return view('filters.assignToReports', [
            'filterDefinition' => $filterDefinition,
            'compatibleReports' => $compatibleReports,
            'assignedReports' => $assignedReports,
        ]);
    }

    /**
     * Update report filter assignments
     */
    public function updateAssignments(Request $request, FilterDefinition $filterDefinition)
    {
        $validated = $request->validate([
            'report_ids' => 'array',
            'report_ids.*' => 'integer|exists:reports,id',
        ]);

        $reportIds = $validated['report_ids'] ?? [];
        $filterDefinition->reports()->sync($reportIds);

        return redirect()->route('filters.show', $filterDefinition)
            ->with('success', 'Filter assignments updated successfully.');
    }

    /**
     * Get all available tables from the database
     */
    private function getAvailableTables(): array
    {
        // Get all tables from the database
        $tables = \Illuminate\Support\Facades\Schema::getTables();

        // Extract table names and filter out system tables
        $tableNames = array_map(function ($table) {
            // Handle both object and array formats depending on database driver
            return $table['name'] ?? $table->name ?? null;
        }, $tables);

        // Filter out null values and system tables
        $tableNames = array_filter($tableNames, function ($name) {
            return $name && ! in_array($name, ['migrations', 'personal_access_tokens', 'password_reset_tokens', 'failed_jobs']);
        });

        // Sort and return
        sort($tableNames);

        return array_values($tableNames);
    }

    /**
     * Get columns for a specific table (API endpoint)
     */
    public function getTableColumns(Request $request)
    {
        $table = $request->input('table');

        if (! $table) {
            return response()->json(['error' => 'Table name required'], 400);
        }

        try {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);

            return response()->json(['columns' => $columns]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Table not found'], 404);
        }
    }

    /**
     * Preview how the filter will look in the frontend
     */
    public function preview(FilterDefinition $filterDefinition)
    {
        // Get options for dynamic filters
        $options = [];

        if ($filterDefinition->options_source === 'dynamic' && $filterDefinition->options_query) {
            try {
                $results = \DB::select($filterDefinition->options_query);
                // Extract the first column value from each result
                if (! empty($results)) {
                    $firstKey = array_key_first((array) $results[0]);
                    $options = array_column($results, $firstKey);
                }
            } catch (\Exception $e) {
                $options = ['Error: Could not fetch options from database'];
            }
        } elseif ($filterDefinition->options_source === 'static' && $filterDefinition->options) {
            // Parse static options
            if (is_array($filterDefinition->options)) {
                $options = $filterDefinition->options;
            } else {
                $optionsText = is_string($filterDefinition->options)
                    ? $filterDefinition->options
                    : json_encode($filterDefinition->options);
                $options = array_filter(array_map('trim', explode("\n", $optionsText)));
            }
        }

        return view('filters.preview', [
            'filterDefinition' => $filterDefinition,
            'options' => $options,
        ]);
    }
}
