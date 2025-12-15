<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Services\Transformers\TransformerFactory;
use DB;

class JoinController extends Controller
{
    private TransformerFactory $transformerFactory;

    public function __construct(TransformerFactory $transformerFactory)
    {
        $this->transformerFactory = $transformerFactory;
    }

    public function index()
    {
        $tableNames = DB::select('SHOW TABLES');
        $tableNames = array_map('current', $tableNames);
        $userNames = User::pluck('name')->all();
        $rawTransformers = $this->transformerFactory->getTransformersByCategory();
        
        // Serialize transformers for frontend
        $transformers = [];
        foreach ($rawTransformers as $category => $items) {
            $transformers[$category] = [];
            foreach ($items as $name => $transformer) {
                $transformers[$category][$name] = [
                    'name' => $transformer->getName(),
                    'description' => $transformer->getDescription()
                ];
            }
        }

        return view('adminViewCreate.index', [
            'tableNames' => $tableNames, 
            'users' => $userNames,
            'transformers' => $transformers
        ]);
    }

    public function fetch(Request $request)
    {
        $value = $request->get('value');
        $dependent = $request->get('dependent');
        $data = DB::getSchemaBuilder()->getColumnListing($value);
        return response()->json([
            'data' => $data,
            'dependent' => $dependent,
            'message' => 'Data retrieved successfully',
        ]);
    }

    public function fetch_datas(Request $request)
    {
        $tableName = $request->get('tableName');
        // $select=$request->get('select');
        $value = $request->get('value');
        $dependent = $request->get('dependent');
        $data = DB::table($tableName)->select($value)->get();
        return response()->json([
            'data' => $data,
            'dependent' => $dependent,
            'message' => 'Data retrieved successfully',
        ]);
    }

    public function fetch_join_datas(Request $request)
    {
        $leftTable = $request->get('leftTable');
        $rightTable = $request->get('rightTable');
        $leftTableColumns = $request->get('leftTableColumns');
        $rightTableColumns = $request->get('rightTableColumns');
        $joinType = $request->get('joinType');
        $leftMatchingColumn = $request->get('leftMatchingColumn');
        $rightMatchingColumn = $request->get('rightMatchingColumn');

        $totalColumns = count($leftTableColumns) + count($rightTableColumns);
        $currentColumnIndex = 0;
        $columnNames = "";
        $sql = "select ";

        foreach ($leftTableColumns as $leftTableColumn) {
            $currentColumnIndex++;
            $columnNames .= ("l." . $leftTableColumn . " as " . $leftTable . "_" . $leftTableColumn);
            if ($currentColumnIndex != $totalColumns) {
                $columnNames .= (",");
            }
        }
        foreach ($rightTableColumns as $rightTableColumn) {
            $currentColumnIndex++;
            $columnNames .= ("r." . $rightTableColumn . " as " . $rightTable . "_" . $rightTableColumn);
            if ($currentColumnIndex != $totalColumns) {
                $columnNames .= (",");
            }
        }
        $sql .= ($columnNames . " from " . $leftTable . " l " . $joinType . " join " . $rightTable . " r");
        if ($joinType !== 'cross') {
            $sql .= (" on l." . $leftMatchingColumn . "=r." . $rightMatchingColumn);
        }
        $data = DB::select($sql);

        return response()->json([
            'data' => $data,
            'message' => 'Data retrieved successfully',
        ]);
    }

    public function processForm(Request $request)
    {
        $users = $request['users'];
        if (empty($request['users'])) {
            $users = [];
        }
        $name = $request['name'];
        $transformations = $request['transformations'] ?? [];
        
        $data = $request->except(['_token', 'table', 'users', 'name', 'transformations']);
        if (!isset($data['joins'])) {
            $data['joins'] = [];
        }
        // dd($data);

        // Restructure transformations to match anticipated format if needed, 
        // but if the form sends them as transformations[table.column][transformers]... it should be fine.
        // We just need to make sure we save it.
        
        // Report::create(['report_details' => $data, 'name' => $name, 'users' => $users]);
        // Update to include column_transformations
         Report::create([
            'report_details' => $data, 
            'name' => $name, 
            'users' => $users,
            'column_transformations' => $transformations
        ]);
        echo "<pre>";

        return redirect('/view-report-list');
    }
}
