<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Services\ReportFilterService;
use App\Services\ReportQueryBuilder;
use App\Services\ColumnTransformationService;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private ReportQueryBuilder $reportQueryBuilder;

    private ReportFilterService $reportFilterService;
    private ColumnTransformationService $transformationService;

    public function __construct(
        ReportQueryBuilder $reportQueryBuilder, 
        ReportFilterService $reportFilterService,
        ColumnTransformationService $transformationService
    ) {
        $this->reportQueryBuilder = $reportQueryBuilder;
        $this->reportFilterService = $reportFilterService;
        $this->transformationService = $transformationService;
    }

    public function showData($id, $startDate = null, $endDate = null)
    {
        $report = Report::find($id);
        if (! $report) {
            return redirect('/view-report-list')->with('error', 'Report not found!');
        }

        $data = $report->report_details;
        $name = $report->name;

        // Get old inline filters (if any)
        $oldFilters = $report->getFilterDefinitions();

        // Get new FilterDefinition filters assigned to this report
        $newFilters = $report->filterDefinitions()->get();

        $result = $this->reportQueryBuilder->build($data).' ';

        // No automatic date range filter - use only dynamic filters

        // Apply old inline filters if provided
        $filterValues = request()->query();
        $bindings = [];
        if (! empty($oldFilters) && ! empty($filterValues)) {
            // Transform number_range single values to min/max format
            foreach ($oldFilters as $filter) {
                if ($filter['type'] === 'number_range' && isset($filterValues[$filter['id']]) && ! is_array($filterValues[$filter['id']])) {
                    $filterValues[$filter['id']] = ['min' => $filterValues[$filter['id']], 'max' => null];
                }
            }

            $filterResult = $this->reportFilterService->applyFilters($result, $oldFilters, $filterValues);
            $result = $filterResult['sql'];
            $bindings = $filterResult['bindings'];
        }

        // Apply new FilterDefinition filters
        if (! empty($newFilters)) {
            foreach ($newFilters as $filter) {
                // Handle conditional filters
                if ($filter->is_conditional) {
                    $selectedTarget = request()->query('filter_'.$filter->id.'_target');

                    if (empty($selectedTarget)) {
                        continue;
                    }

                    // Find the target configuration
                    $targetConfig = collect($filter->conditional_targets)
                        ->firstWhere('key', $selectedTarget);

                    if (! $targetConfig) {
                        continue;
                    }

                    $filterColumn = $targetConfig['table'].'.'.$targetConfig['column'];

                    // Apply filter based on base type
                    if ($filter->type === 'date_range') {
                        $minDate = request()->query('filter_'.$filter->id.'_min');
                        $maxDate = request()->query('filter_'.$filter->id.'_max');

                        if (! empty($minDate)) {
                            $result .= " AND DATE({$filterColumn}) >= ?";
                            $bindings[] = $minDate;
                        }
                        if (! empty($maxDate)) {
                            $result .= " AND DATE({$filterColumn}) <= ?";
                            $bindings[] = $maxDate;
                        }
                    } elseif ($filter->type === 'date') {
                        $filterValue = request()->query('filter_'.$filter->id);
                        if (! empty($filterValue)) {
                            $result .= " AND DATE({$filterColumn}) = ?";
                            $bindings[] = $filterValue;
                        }
                    } elseif ($filter->type === 'number_range') {
                        $minValue = request()->query('filter_'.$filter->id.'_min');
                        $maxValue = request()->query('filter_'.$filter->id.'_max');

                        if (! empty($minValue)) {
                            $result .= " AND {$filterColumn} >= ?";
                            $bindings[] = $minValue;
                        }
                        if (! empty($maxValue)) {
                            $result .= " AND {$filterColumn} <= ?";
                            $bindings[] = $maxValue;
                        }
                    } else {
                        $filterValue = request()->query('filter_'.$filter->id);
                        if (! empty($filterValue)) {
                            $result .= " AND {$filterColumn} = ?";
                            $bindings[] = $filterValue;
                        }
                    }

                    continue;
                }

                // Regular filter logic
                $filterColumn = $filter->target_table.'.'.$filter->target_column;

                // Add filter condition based on filter type
                if ($filter->type === 'number_range') {
                    // Range filter with min and max
                    $minValue = request()->query('filter_'.$filter->id.'_min');
                    $maxValue = request()->query('filter_'.$filter->id.'_max');

                    if (! empty($minValue)) {
                        $result .= " AND {$filterColumn} >= ?";
                        $bindings[] = $minValue;
                    }
                    if (! empty($maxValue)) {
                        $result .= " AND {$filterColumn} <= ?";
                        $bindings[] = $maxValue;
                    }
                } elseif ($filter->type === 'date_range') {
                    // Date range filter
                    $minDate = request()->query('filter_'.$filter->id.'_min');
                    $maxDate = request()->query('filter_'.$filter->id.'_max');

                    if (! empty($minDate)) {
                        $result .= " AND DATE({$filterColumn}) >= ?";
                        $bindings[] = $minDate;
                    }
                    if (! empty($maxDate)) {
                        $result .= " AND DATE({$filterColumn}) <= ?";
                        $bindings[] = $maxDate;
                    }
                } else {
                    $filterValue = request()->query('filter_'.$filter->id);

                    if (empty($filterValue)) {
                        continue;
                    }

                    // Add filter condition based on filter type
                    if ($filter->type === 'dropdown' || $filter->type === 'text') {
                        // Single value filter
                        if (is_array($filterValue)) {
                            $filterValue = $filterValue[0];
                        }
                        $result .= " AND {$filterColumn} = ?";
                        $bindings[] = $filterValue;
                    } elseif ($filter->type === 'number') {
                        $result .= " AND {$filterColumn} = ?";
                        $bindings[] = $filterValue;
                    } elseif ($filter->type === 'date') {
                        $result .= " AND DATE({$filterColumn}) = ?";
                        $bindings[] = $filterValue;
                    } elseif ($filter->type === 'checkbox' || $filter->type === 'multi_select' || $filter->type === 'radio') {
                        // Multiple value filter
                        if (! is_array($filterValue)) {
                            $filterValue = [$filterValue];
                        }
                        if (! empty($filterValue)) {
                            $placeholders = implode(',', array_fill(0, count($filterValue), '?'));
                            $result .= " AND {$filterColumn} IN ({$placeholders})";
                            $bindings = array_merge($bindings, $filterValue);
                        }
                    } elseif ($filter->type === 'autocomplete') {
                        // Autocomplete as single value
                        $result .= " AND {$filterColumn} = ?";
                        $bindings[] = $filterValue;
                    }
                }
            }
        }

        // Execute query to get RAW data (filters applied, transformations NOT yet applied)
        $rawData = DB::select($result, $bindings);
        
        // Apply column transformations for DISPLAY ONLY
        $transformations = $report->getColumnTransformations();
        $displayData = $this->transformationService->applyTransformations(
            $rawData,
            $transformations
        );

        return view('viewReport.index', [
            'data' => $displayData, // Transformed data for display
            'rawData' => $rawData,   // Original data (for exports if needed)
            'name' => $name,
            'oldFilters' => $oldFilters,
            'newFilters' => $newFilters,
            'reportId' => $id,
            'transformations' => $transformations,
        ]);
    }

    public function destroy($id)
    {
        Report::destroy($id);

        return redirect('/view-report-list')->with('flash_message', 'Report deleted!');
    }

    public function edit($id)
    {
        $tableNames = DB::select('SHOW TABLES');
        $tableNames = array_map('current', $tableNames);
        $results = DB::table('reports')
            ->where('id', $id)
            ->select('name', 'report_details', 'users')->get();
        $name = $results[0]->name;
        $report_details = $results[0]->report_details;
        $report_details = json_decode($report_details);
        $selectedTables = [];
        foreach ($report_details->tables as $tables) {
            foreach ($tables as $table => $columns) {
                $allcolumns = DB::getSchemaBuilder()->getColumnListing($table);
                $selectedTables[$table] = $allcolumns;
            }
        }
        $selectedUsers = $results[0]->users;
        $selectedUsers = json_decode($selectedUsers);
        $userNames = User::pluck('name')->all();

        return view('adminViewCreate.edit', ['report_details' => $report_details, 'name' => $name, 'selectedTables' => $selectedTables, 'selectedUsers' => $selectedUsers, 'id' => $id, 'tableNames' => $tableNames, 'users' => $userNames]);
    }

    public function editForm(Request $request, $id)
    {
        $users = $request['users'];
        if (empty($request['users'])) {
            $users = [];
        }
        $name = $request['name'];
        $data = $request->except(['_token', 'table', 'users', 'name']);
        if (! isset($data['joins'])) {
            $data['joins'] = [];
        }
        $updatedData = ['report_details' => $data, 'name' => $name, 'users' => $users];
        $report = Report::find($id);
        $report->update($updatedData);
        echo '<pre>';

        return redirect('/view-report-list');
    }
}
