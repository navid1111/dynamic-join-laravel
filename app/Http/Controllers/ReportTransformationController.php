<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\Transformers\TransformerFactory;
use Illuminate\Http\Request;

class ReportTransformationController extends Controller
{
    private TransformerFactory $transformerFactory;
    
    public function __construct(TransformerFactory $transformerFactory)
    {
        $this->transformerFactory = $transformerFactory;
    }
    
    /**
     * Show transformation configuration page for a report
     */
    public function configure(Report $report)
    {
        $columns = $report->getAllColumns();
        $transformers = $this->transformerFactory->getTransformersByCategory();
        $currentTransformations = $report->getColumnTransformations();
        
        // Fetch Column Types for Validation
        $tables = $report->getUsedTables();
        $columnTypes = [];
        
        foreach ($tables as $table) {
            try {
                $dbColumns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM `{$table}`");
                foreach ($dbColumns as $col) {
                    // Extract base type (e.g. "varchar(255)" -> "varchar")
                    $type = preg_replace('/\(.*\)/', '', $col->Type);
                    $type = explode(' ', $type)[0]; // Handle "int unsigned"
                    $columnTypes["{$table}.{$col->Field}"] = strtolower($type);
                }
            } catch (\Exception $e) {
                // Skip if table not found or error
            }
        }
        
        $typeCompatibility = $this->transformerFactory->getTypeCompatibility();
        
        return view('reports.transformations.configure', [
            'report' => $report,
            'columns' => $columns,
            'transformers' => $transformers,
            'currentTransformations' => $currentTransformations,
            'columnTypes' => $columnTypes,
            'typeCompatibility' => $typeCompatibility,
        ]);
    }
    
    /**
     * Save transformation configuration
     */
    public function store(Request $request, Report $report)
    {
        $validated = $request->validate([
            'transformations' => 'nullable|array',
            'transformations.*.column' => 'required|string',
            'transformations.*.transformers' => 'nullable|array',
            'transformations.*.transformers.*.name' => 'required|string',
            'transformations.*.transformers.*.options' => 'nullable|array',
        ]);
        
        // Restructure to use column as key
        $transformations = [];
        foreach ($validated['transformations'] ?? [] as $config) {
            if (empty($config['transformers'])) {
                continue;
            }
            
            $columnKey = $config['column'];
            $transformations[$columnKey] = [
                'transformers' => $config['transformers']
            ];
        }
        
        $report->update([
            'column_transformations' => $transformations
        ]);
        
        return redirect()->route('viewReport.index', $report->id)
            ->with('success', 'Transformations saved successfully.');
    }
    
    /**
     * Preview transformation on sample data
     */
    public function preview(Request $request)
    {
        $sampleValue = $request->input('value');
        $transformers = $request->input('transformers', []);
        
        // Handle single transformer input for backwards compatibility or single testing
        if (empty($transformers) && $request->has('transformer')) {
            $transformers = [[
                'name' => $request->input('transformer'),
                'options' => $request->input('options', [])
            ]];
        }
        
        $value = $sampleValue;
        
        foreach ($transformers as $config) {
            $value = $this->transformerFactory->transform(
                $config['name'],
                $value,
                $config['options'] ?? []
            );
        }
        
        $result = $value;
        
        return response()->json([
            'original' => $sampleValue,
            'transformed' => $result,
        ]);
    }
    
    /**
     * Get transformer configuration schema
     */
    public function getTransformerConfig(Request $request)
    {
        $transformerName = $request->input('transformer');
        $transformer = $this->transformerFactory->getTransformer($transformerName);
        
        if (!$transformer) {
            return response()->json(['error' => 'Transformer not found'], 404);
        }
        
        return response()->json([
            'name' => $transformer->getName(),
            'description' => $transformer->getDescription(),
            'schema' => $transformer->getConfigSchema(),
        ]);
    }
}
