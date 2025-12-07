<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Services\ReportFilterService;
use App\Services\ReportQueryBuilder;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private ReportQueryBuilder $reportQueryBuilder;
    private ReportFilterService $reportFilterService;

    public function __construct(ReportQueryBuilder $reportQueryBuilder, ReportFilterService $reportFilterService)
    {
        $this->reportQueryBuilder = $reportQueryBuilder;
        $this->reportFilterService = $reportFilterService;
    }

    public function showData($id, $startDate = null, $endDate = null)
    {
        $report = Report::find($id);
        if (!$report) {
            return redirect('/view-report-list')->with('error', 'Report not found!');
        }

        // Set default dates if not provided
        if ($startDate === null && $endDate === null) {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-6 months'));
        }

        $data = $report->report_details;
        $name = $report->name;
        
        // Get old inline filters (if any)
        $oldFilters = $report->getFilterDefinitions();
        
        // Get new FilterDefinition filters assigned to this report
        $newFilters = $report->filterDefinitions()->get();

        $result = $this->reportQueryBuilder->build($data).' ';
        
        // Add date range filter only if dateTable exists
        if (isset($data['dateTable'])) {
            $result .= 'where date('.$data['dateTable'].".created_at) between '".$startDate."' and '".$endDate."'";
        } elseif (!empty($data['tables'])) {
            $result .= 'where date('.key($data['tables'][0]).".created_at) between '".$startDate."' and '".$endDate."'";
        }

        // Apply old inline filters if provided
        $filterValues = request()->query();
        $bindings = [];
        if (!empty($oldFilters) && !empty($filterValues)) {
            // Transform number_range single values to min/max format
            foreach ($oldFilters as $filter) {
                if ($filter['type'] === 'number_range' && isset($filterValues[$filter['id']]) && !is_array($filterValues[$filter['id']])) {
                    $filterValues[$filter['id']] = ['min' => $filterValues[$filter['id']], 'max' => null];
                }
            }

            $filterResult = $this->reportFilterService->applyFilters($result, $oldFilters, $filterValues);
            $result = $filterResult['sql'];
            $bindings = $filterResult['bindings'];
        }

        // Apply new FilterDefinition filters
        if (!empty($newFilters)) {
            foreach ($newFilters as $filter) {
                $filterValue = request()->query('filter_' . $filter->id);
                
                if (empty($filterValue)) {
                    continue;
                }

                // Determine the column to filter on
                $filterColumn = $filter->target_table . '.' . $filter->target_column;

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
                } elseif ($filter->type === 'checkbox' || $filter->type === 'multi_select') {
                    // Multiple value filter
                    if (!is_array($filterValue)) {
                        $filterValue = [$filterValue];
                    }
                    if (!empty($filterValue)) {
                        $placeholders = implode(',', array_fill(0, count($filterValue), '?'));
                        $result .= " AND {$filterColumn} IN ({$placeholders})";
                        $bindings = array_merge($bindings, $filterValue);
                    }
                }
            }
        }

        $result = DB::select($result, $bindings);

        return view('viewReport.index', [
            'data' => $result,
            'name' => $name,
            'oldFilters' => $oldFilters,
            'newFilters' => $newFilters,
            'reportId' => $id,
            'startDate' => $startDate,
            'endDate' => $endDate,
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
