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

        <form action="{{ route('filters.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
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

            <div class="mb-4">
                <label for="target_table" class="block text-gray-700 font-bold mb-2">Target Table *</label>
                <select id="target_table" name="target_table"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    required onchange="loadTableColumns()">
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
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    required>
                    <option value="">-- Select Column --</option>
                </select>
                @error('target_column')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
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
                                            {{ $table }}</option>
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
                        <label for="options" class="block text-gray-700 font-bold mb-2">Manual Options (one per
                            line)</label>
                        <textarea id="options" name="options"
                            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500 font-mono text-sm"
                            rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3">{{ old('options') }}</textarea>
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

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            updateOptionsField();
            updateOptionsSourceField();
            loadTableColumns();
        });
    </script>
</body>

</html>