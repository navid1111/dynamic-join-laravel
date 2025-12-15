<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Report</title>
    <script type="module" src="{{ asset('js/columnNameFetcher.mjs') }}" defer></script>
    <script type="module" src="{{ asset('js/addTables.mjs') }}" defer></script>
    <link rel="stylesheet" href="{{ asset('modules/prism.min.css') }}">
    <script src="{{ asset('modules/jquery.min.js') }}"></script>
    <script src="{{ asset('modules/bootstrap.min.js') }}"></script>
    <script src="{{ asset('modules/bootstrap2.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('modules/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/create.css') }}">
    @routes
    <script>
        window.transformers = @json($transformers);
    </script>
    <style>
        .transformation-row {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            transition: all 0.3s ease;
        }
        .transformer-select {
            width: 100%;
        }
    </style>
</head>

<body>
    <div class="container center-button">
        <div class="container-bg row">
            <h1 class="mb-4">Create Report</h1>
            <form action="{{ url('/') }}/create-report" method="post">
                @csrf
                <div class="shadow-line p-3 mb-5 rounded">
                    <div>
                        <input type="text" name="name" class="form-control mt-4" id="reportName"
                            placeholder="Report Name" required>
                    </div>
                    <div>
                        <select name="users[]" id="users" class="form-select mt-4 mb-4" multiple>
                            <option disabled value="">Select Users</option>
                            @foreach ($users as $user)
                                <option value="{{ $user }}">{{ $user }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="tablesDiv" class="mb-4">
                    <div id="dynamicDiv0" class="tables shadow-line close-button p-3 mb-5 rounded">
                        <div class="g-3">
                            <label for="table0" class="form-label"></label>
                            <select name="table[]" id="table0" class="form-select mt-0 dynamic"
                                data-dependent="tableColumns0" dependent="tableColumns0" required>
                                <option disabled selected value="">Select Table</option>
                                @foreach ($tableNames as $tableName)
                                    <option value="{{ $tableName }}">{{ $tableName }}</option>
                                @endforeach
                            </select>
                            <label for="tableColumns0" class="form-label"></label>
                            <select name="tables" id="tableColumns0"
                                class="form-select mt-0 mb-4 dynamicdatas tableColumnChanged"
                                data-dependent="tableDatas0" required multiple>
                                <option value="">Select Columns</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <select name="dateTable" id="dateTable" class="form-select mt-0" required>
                        <option disabled selected value="">Select Table</option>
                    </select>
                </div>

                <div id="transformation-section" class="mt-4 shadow-line p-3 mb-4 rounded" style="display: none;">
                    <h4>Column Transformations</h4>
                    <p class="text-muted small">Apply transformations to columns (e.g., Uppercase, Date Format).</p>
                    <div id="transformations-container"></div>
                </div>
                <div id="addTableDiv" class="mt-4" type="button">
                    <button id="addTable" class="btn btn-secondary px-2 py-0"> + </button>
                </div>
                <div class="center-button">
                    <button type="submit" class="btn btn-info text-white px-2">Submit</button>
                </div>
                {{ csrf_field() }}
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const container = document.getElementById('transformations-container');
            const section = document.getElementById('transformation-section');
            const transformers = window.transformers;

            function renderTransformationRows() {
                container.innerHTML = '';
                let hasColumns = false;
                
                // Process main table columns (tableColumns0, tableColumns1, etc.)
                document.querySelectorAll('.tableColumnChanged').forEach(select => {
                    const tableSelectId = select.id.replace('tableColumns', 'table');
                    const tableSelect = document.getElementById(tableSelectId);
                    const tableName = tableSelect ? tableSelect.value : '';
                    
                    if (tableName && select.selectedOptions.length > 0) {
                        Array.from(select.selectedOptions).forEach(option => {
                            if(option.value) {
                                hasColumns = true;
                                addTransformationRow(tableName, option.value);
                            }
                        });
                    }
                });

                // Process join columns? (Users might want to transform join columns too)
                // The current Join logic in addTables.mjs is complex. 
                // For MVP, lets focus on the main selected columns in the "Select Columns" dropdowns.
                // The 'tableColumnChanged' class seems to target the main column selectors.

                section.style.display = hasColumns ? 'block' : 'none';
            }

            function addTransformationRow(table, column) {
                const key = `${table}.${column}`; // Use dot notation for key
                // Ideally we use a unique ID, but table.column is unique per report config usually
                
                const row = document.createElement('div');
                row.className = 'transformation-row p-3 mb-2';
                
                let transformerOptions = '<option value="">No Transformation</option>';
                for (const [category, items] of Object.entries(transformers)) {
                    transformerOptions += `<optgroup label="${category}">`;
                    for (const [name, transformer] of Object.entries(items)) {
                        /* 
                           Note: transformer object structure from PHP might be array or object. 
                           Based on Factory: $transformers[$name] = $transformer object.
                           json_encode of object methods might not work directly if they are not public properties.
                           
                           Wait, json_encode($transformers) where elements are Objects... 
                           Private/Protected properties won't show. Methods won't show.
                           
                           I need detailed info about transformers (name, description). 
                           The Factory returns instances.
                           
                           I should probably have serialized them in the controller.
                           Let's fix the Controller to return an array of definitions first.
                        */
                       // Fallback if data is missing, but I'll update controller in a sec.
                       // Assuming I fix controller to return ['name' => ..., 'description' => ...]
                    }
                }
                // Placeholder until controller update
            }
            
            // Listen for changes
            document.addEventListener('change', function(e) {
                if(e.target.classList.contains('tableColumnChanged') || e.target.classList.contains('dynamic')) {
                    // Slight delay to allow other simple scripts to run
                    setTimeout(updateTransformations, 100);
                }
            });

            const state = {}; // Store selected transformers to persist across re-renders

            function updateTransformations() {
                const currentSelections = {};
                
                // Collect currently selected columns
                document.querySelectorAll('.tableColumnChanged').forEach(select => {
                     const tableSelectId = select.id.replace('tableColumns', 'table');
                     const tableSelect = document.getElementById(tableSelectId);
                     const tableName = tableSelect ? tableSelect.value : '';
                     
                     if (tableName && select.selectedOptions.length > 0) {
                        Array.from(select.selectedOptions).forEach(option => {
                            if(option.value) {
                                const key = `${tableName}_${option.value}`; // Unique key
                                currentSelections[key] = {
                                    table: tableName,
                                    column: option.value,
                                    display: `${tableName}.${option.value}`
                                };
                            }
                        });
                     }
                });

                // Render
                section.style.display = Object.keys(currentSelections).length > 0 ? 'block' : 'none';
                
                // Remove stale
                Array.from(container.children).forEach(child => {
                    if (!currentSelections[child.dataset.key]) {
                        child.remove();
                        delete state[child.dataset.key];
                    }
                });

                // Add new
                Object.values(currentSelections).forEach(item => {
                    const key = item.key || `${item.table}_${item.column}`; // handle undefined?
                    // Actually key used in selections is unique
                    const uniqueKey = `${item.table}_${item.column}`;
                    
                    if (!document.querySelector(`.transformation-row[data-key="${uniqueKey}"]`)) {
                        const row = createRow(item.table, item.column, uniqueKey);
                        container.appendChild(row);
                    }
                });
            }

            function createRow(table, column, uniqueKey) {
                const row = document.createElement('div');
                row.className = 'transformation-row p-3 mb-2';
                row.dataset.key = uniqueKey;
                
                // Generate Options
                let optionsHtml = '<option value="">None</option>';
                for (const [category, items] of Object.entries(transformers)) {
                    optionsHtml += `<optgroup label="${category}">`;
                    for (const [name, details] of Object.entries(items)) {
                        optionsHtml += `<option value="${name}">${details.description || name}</option>`;
                    }
                    optionsHtml += `</optgroup>`;
                }

                // Field Name map to existing structure:
                // We want to save: column_transformations = { "col_key": { "transformers": [ { "name": "uppercase" } ] } }
                // The column key in the report data usually matches the alias used in query.
                // In JoinController, columns are aliased as `TableName_ColumnName`.
                // So the key should be `TableName_ColumnName`.
                
                const fieldName = `transformations[${table}_${column}][transformers][0][name]`;
                
                row.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <strong>${table}.${column}</strong>
                        </div>
                        <div class="col-md-8">
                             <select class="form-select" name="${fieldName}">
                                ${optionsHtml}
                             </select>
                        </div>
                    </div>
                `;
                return row;
            }
        });
    </script>
</body>

</html>
