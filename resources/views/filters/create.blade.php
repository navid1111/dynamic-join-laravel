<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Filter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Create New Filter</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('filters.store') }}" method="POST" class="bg-white rounded-lg shadow p-6"
            onsubmit="return handleFormSubmit(event)">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-bold mb-2">Filter Name *</label>
                <input type="text" id="name" name="name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    placeholder="e.g., marks_filter" value="{{ old('name') }}" required>
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="label" class="block text-gray-700 font-bold mb-2">Display Label *</label>
                <input type="text" id="label" name="label"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    placeholder="e.g., Filter by Marks" value="{{ old('label') }}" required>
                @error('label')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="type" class="block text-gray-700 font-bold mb-2">Filter Type *</label>
                <select id="type" name="type"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    required onchange="updateOptionsField()">
                    <option value="">-- Select Type --</option>
                    @foreach($filterTypes as $key => $value)
                        <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
                @error('type')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4 flex items-center">
                <input type="hidden" name="is_conditional" value="0">
                <input type="checkbox" id="is_conditional" name="is_conditional" value="1" class="mr-2" {{ old('is_conditional') ? 'checked' : '' }} onchange="toggleConditionalFields()">
                <label for="is_conditional" class="text-gray-700 font-bold">Conditional Filter</label>
                <span class="ml-2 text-gray-500 text-sm">(Allow users to switch between different table/column
                    targets)</span>
            </div>

            <!-- Regular Target Fields -->
            <div id="regularTargetFields" style="{{ old('is_conditional') ? 'display: none;' : '' }}">
                <div class="mb-4">
                    <label for="target_table" class="block text-gray-700 font-bold mb-2">Target Table *</label>
                    <select id="target_table" name="target_table"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        onchange="loadTableColumns()">
                        <option value="">-- Select Table --</option>
                        @foreach($tables as $table)
                            <option value="{{ $table }}" {{ old('target_table') === $table ? 'selected' : '' }}>{{ $table }}
                            </option>
                        @endforeach
                    </select>
                    @error('target_table')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="target_column" class="block text-gray-700 font-bold mb-2">Target Column *</label>
                    <select id="target_column" name="target_column"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                        <option value="">-- Select Column --</option>
                    </select>
                    @error('target_column')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Conditional Target Fields -->
            <div id="conditionalFields" style="{{ old('is_conditional') ? '' : 'display: none;' }}" class="mb-4">
                <div class="p-4 bg-purple-50 border border-purple-200 rounded">
                    <h3 class="font-bold text-gray-800 mb-3">Conditional Targets Configuration</h3>

                    <div class="mb-4">
                        <label for="conditional_type" class="block text-gray-700 font-bold mb-2">Display Type *</label>
                        <select id="conditional_type" name="conditional_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                            <option value="">-- Select Display Type --</option>
                            @foreach($conditionalTypes as $key => $value)
                                <option value="{{ $key }}" {{ old('conditional_type') === $key ? 'selected' : '' }}>
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                        @error('conditional_type')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                        <p class="text-gray-500 text-sm mt-1">How the filter options will be displayed (buttons, tabs,
                            or dropdown)</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Target Options</label>
                        <div id="conditionalTargetsList" class="space-y-3">
                            @if(old('conditional_targets'))
                                @foreach(old('conditional_targets') as $index => $target)
                                    <div class="conditional-target-row border border-gray-300 rounded p-3">
                                        <div class="grid grid-cols-2 gap-3 mb-2">
                                            <div>
                                                <label class="block text-gray-600 text-sm mb-1">Key (ID) *</label>
                                                <input type="text" name="conditional_targets[{{ $index }}][key]"
                                                    placeholder="e.g., booking_date"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                                    value="{{ $target['key'] ?? '' }}">
                                            </div>
                                            <div>
                                                <label class="block text-gray-600 text-sm mb-1">Label (Display) *</label>
                                                <input type="text" name="conditional_targets[{{ $index }}][label]"
                                                    placeholder="e.g., Booking Date"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                                    value="{{ $target['label'] ?? '' }}">
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-gray-600 text-sm mb-1">Table *</label>
                                                <select name="conditional_targets[{{ $index }}][table]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 conditional-target-table"
                                                    data-row="{{ $index }}"
                                                    onchange="loadConditionalColumns(this, {{ $index }})">
                                                    <option value="">-- Select Table --</option>
                                                    @foreach($tables as $table)
                                                        <option value="{{ $table }}" {{ ($target['table'] ?? '') === $table ? 'selected' : '' }}>{{ $table }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-gray-600 text-sm mb-1">Column *</label>
                                                <select name="conditional_targets[{{ $index }}][column]"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 conditional-target-column-{{ $index }}">
                                                    <option value="">-- Select Column --</option>
                                                    @if(isset($target['column']))
                                                        <option value="{{ $target['column'] }}" selected>{{ $target['column'] }}
                                                        </option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <button type="button" onclick="removeConditionalTarget(this)"
                                            class="mt-2 bg-red-500 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">Remove</button>
                                    </div>
                                @endforeach
                            @else
                                <div class="conditional-target-row border border-gray-300 rounded p-3">
                                    <div class="mb-2">
                                        <label class="block text-gray-600 text-sm mb-1">Label (Display Name) *</label>
                                        <input type="text" name="conditional_targets[0][label]"
                                            placeholder="e.g., Booking Date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                            onchange="updateConditionalKey(this, 0)">
                                        <input type="hidden" name="conditional_targets[0][key]" class="conditional-key-0">
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-gray-600 text-sm mb-1">Table *</label>
                                            <select name="conditional_targets[0][table]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 conditional-target-table"
                                                data-row="0" onchange="loadConditionalColumns(this, 0)">
                                                <option value="">-- Select Table --</option>
                                                @foreach($tables as $table)
                                                    <option value="{{ $table }}">{{ $table }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-gray-600 text-sm mb-1">Column *</label>
                                            <select name="conditional_targets[0][column]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 conditional-target-column-0">
                                                <option value="">-- Select Column --</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" onclick="removeConditionalTarget(this)"
                                        class="mt-2 bg-red-500 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">Remove</button>
                                </div>
                            @endif
                        </div>
                        <button type="button" onclick="addConditionalTarget()"
                            class="mt-3 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            + Add Target Option
                        </button>
                        <p class="text-gray-500 text-sm mt-2">Add multiple table/column combinations users can switch
                            between</p>
                    </div>
                </div>
            </div>

            <!-- Options Fields - Only shown for types that need them -->
            <div id="optionsSection" style="display: none;">
                <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded">
                    <h3 class="font-bold text-gray-800 mb-3">Options Configuration</h3>

                    <div class="mb-4">
                        <label for="options_source" class="block text-gray-700 font-bold mb-2">Options Source *</label>
                        <select id="options_source" name="options_source"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                            onchange="updateOptionsSourceField()">
                            <option value="dynamic" {{ old('options_source') === 'dynamic' ? 'selected' : '' }}>From
                                Database Column</option>
                            <option value="static" {{ old('options_source') === 'static' ? 'selected' : '' }}>Manual List
                            </option>
                        </select>
                    </div>

                    <div id="dynamicOptionsField" class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Select Table & Column for Options</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="options_table" class="block text-gray-600 text-sm mb-1">Table *</label>
                                <select id="options_table" name="options_table"
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    onchange="loadOptionsColumns()">
                                    <option value="">-- Select Table --</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table }}" {{ old('options_table') === $table ? 'selected' : '' }}>
                                            {{ $table }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="options_column" class="block text-gray-600 text-sm mb-1">Column *</label>
                                <select id="options_column" name="options_column"
                                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                                    <option value="">-- Select Column --</option>
                                </select>
                            </div>
                        </div>
                        <p class="text-gray-500 text-sm mt-2">Options will be populated from distinct values in this
                            column</p>
                    </div>

                    <div id="staticOptionsField" style="display: none;">
                        <label for="options" class="block text-gray-700 font-bold mb-2">Static Options (Key: Value
                            pairs)</label>
                        <div class="bg-white p-4 border border-gray-300 rounded">
                            <div id="optionsList" class="space-y-3">
                                <div class="option-row flex gap-2">
                                    <input type="text" name="option_keys[]" placeholder="Key (e.g., 40002)"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                                    <input type="text" name="option_values[]" placeholder="Value (e.g., embdad)"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                                    <button type="button" onclick="removeOption(this)"
                                        class="bg-red-500 hover:bg-red-700 text-white px-3 py-2 rounded">Remove</button>
                                </div>
                            </div>
                            <button type="button" onclick="addOption()"
                                class="mt-3 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                + Add Option
                            </button>
                            <p class="text-gray-500 text-sm mt-2">Add key-value pairs for your filter options</p>
                        </div>
                        @error('options')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="placeholder" class="block text-gray-700 font-bold mb-2">Placeholder Text</label>
                <input type="text" id="placeholder" name="placeholder"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    placeholder="e.g., Select marks..." value="{{ old('placeholder') }}">
            </div>

            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea id="description" name="description"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    rows="3" placeholder="Describe what this filter does...">{{ old('description') }}</textarea>
            </div>

            <div class="mb-4 flex items-center">
                <input type="hidden" name="required" value="0">
                <input type="checkbox" id="required" name="required" value="1" class="mr-2" {{ old('required') ? 'checked' : '' }}>
                <label for="required" class="text-gray-700 font-bold">Required Filter</label>
            </div>

            <div class="mb-6 flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="mr-2" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" class="text-gray-700 font-bold">Active</label>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('filters.index') }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create Filter
                </button>
            </div>
        </form>
    </div>

    <script>
        const typesWithOptions = {!! json_encode($typesWithOptions) !!};
        let conditionalTargetIndex = {{ old('conditional_targets') ? count(old('conditional_targets')) : 1 }};

        function toggleConditionalFields() {
            const isConditional = document.getElementById('is_conditional').checked;
            const conditionalFields = document.getElementById('conditionalFields');
            const regularFields = document.getElementById('regularTargetFields');
            const targetTable = document.getElementById('target_table');
            const targetColumn = document.getElementById('target_column');

            if (isConditional) {
                conditionalFields.style.display = 'block';
                regularFields.style.display = 'none';
                targetTable.removeAttribute('required');
                targetColumn.removeAttribute('required');
            } else {
                conditionalFields.style.display = 'none';
                regularFields.style.display = 'block';
                targetTable.setAttribute('required', 'required');
                targetColumn.setAttribute('required', 'required');
            }
        }

        function addConditionalTarget() {
            const targetsList = document.getElementById('conditionalTargetsList');
            const newRow = document.createElement('div');
            newRow.className = 'conditional-target-row border border-gray-300 rounded p-3';
            newRow.innerHTML = `
                <div class="mb-2">
                    <label class="block text-gray-600 text-sm mb-1">Label (Display Name) *</label>
                    <input type="text" name="conditional_targets[${conditionalTargetIndex}][label]" 
                        placeholder="e.g., Booking Date"
                        class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                        onchange="updateConditionalKey(this, ${conditionalTargetIndex})">
                    <input type="hidden" name="conditional_targets[${conditionalTargetIndex}][key]" class="conditional-key-${conditionalTargetIndex}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-gray-600 text-sm mb-1">Table *</label>
                        <select name="conditional_targets[${conditionalTargetIndex}][table]"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 conditional-target-table"
                            data-row="${conditionalTargetIndex}" onchange="loadConditionalColumns(this, ${conditionalTargetIndex})">
                            <option value="">-- Select Table --</option>
                            ${Array.from(document.getElementById('target_table').options)
                    .map(opt => opt.value ? `<option value="${opt.value}">${opt.value}</option>` : '')
                    .join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm mb-1">Column *</label>
                        <select name="conditional_targets[${conditionalTargetIndex}][column]"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 conditional-target-column-${conditionalTargetIndex}">
                            <option value="">-- Select Column --</option>
                        </select>
                    </div>
                </div>
                <button type="button" onclick="removeConditionalTarget(this)"
                    class="mt-2 bg-red-500 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">Remove</button>
            `;
            targetsList.appendChild(newRow);
            conditionalTargetIndex++;
        }

        function removeConditionalTarget(button) {
            button.closest('.conditional-target-row').remove();
        }

        function updateConditionalKey(labelInput, rowIndex) {
            const label = labelInput.value;
            const key = label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
            const keyInput = document.querySelector('.conditional-key-' + rowIndex);
            if (keyInput) {
                keyInput.value = key;
            }
        }

        function loadConditionalColumns(selectElement, rowIndex) {
            const table = selectElement.value;
            const columnSelect = document.querySelector(`.conditional-target-column-${rowIndex}`);

            if (!table) {
                columnSelect.innerHTML = '<option value="">-- Select Column --</option>';
                return;
            }

            fetch(`/filters/api/table-columns?table=${encodeURIComponent(table)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.columns) {
                        columnSelect.innerHTML = '<option value="">-- Select Column --</option>';
                        data.columns.forEach(col => {
                            const option = document.createElement('option');
                            option.value = col;
                            option.textContent = col;
                            columnSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function updateOptionsField() {
            const type = document.getElementById('type').value;
            const optionsSection = document.getElementById('optionsSection');
            optionsSection.style.display = typesWithOptions.includes(type) ? 'block' : 'none';
        }

        function loadTableColumns() {
            const table = document.getElementById('target_table').value;
            const columnSelect = document.getElementById('target_column');

            if (!table) {
                columnSelect.innerHTML = '<option value="">-- Select Column --</option>';
                return;
            }

            fetch(`/filters/api/table-columns?table=${encodeURIComponent(table)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.columns) {
                        columnSelect.innerHTML = '<option value="">-- Select Column --</option>';
                        data.columns.forEach(col => {
                            const option = document.createElement('option');
                            option.value = col;
                            option.textContent = col;
                            columnSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function loadOptionsColumns() {
            const table = document.getElementById('options_table').value;
            const columnSelect = document.getElementById('options_column');

            if (!table) {
                columnSelect.innerHTML = '<option value="">-- Select Column --</option>';
                return;
            }

            fetch(`/filters/api/table-columns?table=${encodeURIComponent(table)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.columns) {
                        columnSelect.innerHTML = '<option value="">-- Select Column --</option>';
                        data.columns.forEach(col => {
                            const option = document.createElement('option');
                            option.value = col;
                            option.textContent = col;
                            columnSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function updateOptionsSourceField() {
            const source = document.getElementById('options_source').value;
            document.getElementById('dynamicOptionsField').style.display = source === 'dynamic' ? 'block' : 'none';
            document.getElementById('staticOptionsField').style.display = source === 'static' ? 'block' : 'none';
        }

        function addOption() {
            const optionsList = document.getElementById('optionsList');
            const newRow = document.createElement('div');
            newRow.className = 'option-row flex gap-2';
            newRow.innerHTML = `
                <input type="text" name="option_keys[]" placeholder="Key (e.g., 40002)" 
                    class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                <input type="text" name="option_values[]" placeholder="Value (e.g., embdad)" 
                    class="flex-1 px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                <button type="button" onclick="removeOption(this)" 
                    class="bg-red-500 hover:bg-red-700 text-white px-3 py-2 rounded">Remove</button>
            `;
            optionsList.appendChild(newRow);
        }

        function removeOption(button) {
            button.parentElement.remove();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            updateOptionsField();
            updateOptionsSourceField();
            loadTableColumns();
            toggleConditionalFields();
        });

        function handleFormSubmit(event) {
            const isConditional = document.getElementById('is_conditional').checked;

            if (isConditional) {
                // Clear regular target fields to prevent sending string "null"
                document.getElementById('target_table').value = '';
                document.getElementById('target_column').value = '';
            } else {
                // Clear conditional fields
                const conditionalTargetInputs = document.querySelectorAll('input[name^="conditional_targets"], select[name^="conditional_targets"]');
                conditionalTargetInputs.forEach(input => input.remove());

                const conditionalType = document.getElementById('conditional_type');
                if (conditionalType) {
                    conditionalType.value = '';
                }
            }

            return true;
        }
    </script>
</body>

</html>