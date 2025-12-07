<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Data</title>

    {{-- <!-- Bootstrap CSS -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <!-- Add this script tag to include json-formatter-js directly in your HTML -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/components/prism-json.min.js"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/themes/prism.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.27.0/prism.min.js"></script> --}}
    <link rel="stylesheet" href="{{ asset('modules/prism.min.css') }}">
    <script src="{{ asset('modules/jquery.min.js') }}"></script>
    <script src="{{ asset('modules/jquery.slim.min.js') }}"></script>
    <script src="{{ asset('modules/bootstrap.min.js') }}"></script>
    <script src="{{ asset('modules/bootstrap2.min.js') }}"></script>
    <script src="{{ asset('modules/alpine.min.js') }}" defer></script>
    <script src="{{ asset('modules/popper.min.js') }}"></script>
    <script src="{{ asset('modules/prism.js') }}"></script>
    <script src="{{ asset('modules/prism.min.js') }}"></script>
    <script src="{{ asset('modules/prism-json.min.js') }}"></script>
    <script src="{{ asset('modules/cdn.min.js') }}"></script>
    <script src="{{ asset('modules/dataTable.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('modules/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/bootstrap2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/bootstrap3.min.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/dataTable.css') }}">
    <link rel="stylesheet" href="{{ asset('css/container.css') }}">
    <script src="{{ asset('js/download.js') }}"></script>
    <script src="{{ asset('js/changeDate.js') }}"></script>
    @routes
</head>

<body>
    <div class="container mt-5">
        <div class="flex justify-between items-center bg-gray-200 p-5 rounded-md">
            <h1>
                @php
                    echo $name;
                @endphp
            </h1>

            <!-- Date Filter Form (Optional) -->
            <form id="dateForm" class="mt-4">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-5 col-sm-6">
                        <label for="startDate">Start Date:</label>
                        <input type="date" class="form-control" id="startDate" name="startDate" value="{{ $startDate }}">
                    </div>
                    <div class="form-group col-md-5 col-sm-6">
                        <label for="endDate">End Date:</label>
                        <input type="date" class="form-control" id="endDate" name="endDate" value="{{ $endDate }}">
                    </div>
                    <div class="form-group col-md-2 col-sm-12">
                        <a id="changeDateBtn" class="btn btn-primary w-100 mt-md-0 mt-4"
                            onclick="showAllData()">View</a>
                    </div>
                </div>
            </form>

            <!-- Old Inline Filters -->
            @if (!empty($oldFilters))
                <div class="bg-white p-4 rounded-md mt-4 border border-gray-300">
                    <h5>Inline Filters</h5>
                    <form id="filterForm" method="GET">
                        <div class="form-row">
                            @foreach ($oldFilters as $filter)
                                @if ($filter['type'] === 'dropdown')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter['id'] }}">{{ $filter['label'] }}</label>
                                        <select class="form-control" id="filter_{{ $filter['id'] }}" name="{{ $filter['id'] }}">
                                            <option value="">-- Select --</option>
                                            @if ($filter['options_source'] === 'static')
                                                @foreach ($filter['options'] as $option)
                                                    <option value="{{ $option }}"
                                                        @if (request()->query($filter['id']) === $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            @elseif ($filter['options_source'] === 'query')
                                                @php
                                                    $options = \Illuminate\Support\Facades\DB::select($filter['options_query']);
                                                @endphp
                                                @foreach ($options as $option)
                                                    @php
                                                        $optionValue = $option->name ?? current((array) $option);
                                                    @endphp
                                                    <option value="{{ $optionValue }}"
                                                        @if (request()->query($filter['id']) === $optionValue) selected @endif>
                                                        {{ $optionValue }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @elseif ($filter['type'] === 'number_range')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_range_{{ $filter['id'] }}">{{ $filter['label'] }}</label>
                                        <input type="number" class="form-control" id="filter_range_{{ $filter['id'] }}"
                                            name="{{ $filter['id'] }}" placeholder="Enter value"
                                            value="{{ request()->query($filter['id']) }}">
                                    </div>
                                @elseif ($filter['type'] === 'date_range')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_date_{{ $filter['id'] }}">{{ $filter['label'] }}</label>
                                        <input type="date" class="form-control" id="filter_date_{{ $filter['id'] }}"
                                            name="{{ $filter['id'] }}" value="{{ request()->query($filter['id']) }}">
                                    </div>
                                @elseif ($filter['type'] === 'text')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_text_{{ $filter['id'] }}">{{ $filter['label'] }}</label>
                                        <input type="text" class="form-control" id="filter_text_{{ $filter['id'] }}"
                                            name="{{ $filter['id'] }}" placeholder="Search..."
                                            value="{{ request()->query($filter['id']) }}">
                                    </div>
                                @elseif ($filter['type'] === 'checkbox')
                                    <div class="form-group col-md-12">
                                        <label>{{ $filter['label'] }}</label>
                                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                            @if ($filter['options_source'] === 'static')
                                                @foreach ($filter['options'] as $option)
                                                    @php
                                                        $selectedValues = request()->query($filter['id'], []);
                                                        $isChecked = is_array($selectedValues) && in_array($option, $selectedValues);
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="filter_check_{{ $filter['id'] }}_{{ $loop->index }}"
                                                            name="{{ $filter['id'] }}[]" value="{{ $option }}"
                                                            @if ($isChecked) checked @endif>
                                                        <label class="form-check-label"
                                                            for="filter_check_{{ $filter['id'] }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @elseif ($filter['options_source'] === 'query')
                                                @php
                                                    $checkboxOptions = \Illuminate\Support\Facades\DB::select($filter['options_query']);
                                                    $selectedValues = request()->query($filter['id'], []);
                                                @endphp
                                                @foreach ($checkboxOptions as $option)
                                                    @php
                                                        $optionValue = $option->name ?? current((array) $option);
                                                        $isChecked = is_array($selectedValues) && in_array($optionValue, $selectedValues);
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="filter_check_{{ $filter['id'] }}_{{ $loop->index }}"
                                                            name="{{ $filter['id'] }}[]" value="{{ $optionValue }}"
                                                            @if ($isChecked) checked @endif>
                                                        <label class="form-check-label"
                                                            for="filter_check_{{ $filter['id'] }}_{{ $loop->index }}">
                                                            {{ $optionValue }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            <div class="form-group col-md-12 mt-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Apply Filters</button>
                                    <button type="button" class="btn btn-secondary" onclick="clearFilters()">Clear Filters</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <!-- New FilterDefinition Filters -->
            @if (!empty($newFilters) && count($newFilters) > 0)
                <div class="bg-white p-4 rounded-md mt-4 border border-blue-300">
                    <h5 class="mb-3">
                        <span class="badge badge-info">NEW</span> Dynamic Filters ({{ count($newFilters) }} filters)
                    </h5>
                    <form id="dynamicFilterForm" method="GET">
                        <div class="form-row">
                            @foreach ($newFilters as $filter)
                                @if ($filter->type === 'dropdown')
                                    <div class="form-group col-lg-3 col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter->id }}">{{ $filter->label }}</label>
                                        <select class="form-control" id="filter_{{ $filter->id }}" name="filter_{{ $filter->id }}">
                                            <option value="">-- Select --</option>
                                            @if ($filter->options_source === 'static' && !empty($filter->options))
                                                @foreach ($filter->options as $option)
                                                    <option value="{{ $option }}"
                                                        @if (request()->query('filter_' . $filter->id) === $option) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            @elseif ($filter->options_source === 'dynamic')
                                                @php
                                                    try {
                                                        $options = \Illuminate\Support\Facades\DB::select($filter->options_query);
                                                    } catch (\Exception $e) {
                                                        $options = [];
                                                    }
                                                @endphp
                                                @foreach ($options as $option)
                                                    @php
                                                        $optionValue = $option->{$filter->options_column} ?? current((array) $option);
                                                    @endphp
                                                    <option value="{{ $optionValue }}"
                                                        @if (request()->query('filter_' . $filter->id) === $optionValue) selected @endif>
                                                        {{ $optionValue }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @elseif ($filter->type === 'text')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter->id }}">{{ $filter->label }}</label>
                                        <input type="text" class="form-control" id="filter_{{ $filter->id }}"
                                            name="filter_{{ $filter->id }}" placeholder="{{ $filter->placeholder ?? 'Search...' }}"
                                            value="{{ request()->query('filter_' . $filter->id) }}">
                                    </div>
                                @elseif ($filter->type === 'number')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter->id }}">{{ $filter->label }}</label>
                                        <input type="number" class="form-control" id="filter_{{ $filter->id }}"
                                            name="filter_{{ $filter->id }}" placeholder="{{ $filter->placeholder ?? 'Enter number' }}"
                                            value="{{ request()->query('filter_' . $filter->id) }}">
                                    </div>
                                @elseif ($filter->type === 'date')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter->id }}">{{ $filter->label }}</label>
                                        <input type="date" class="form-control" id="filter_{{ $filter->id }}"
                                            name="filter_{{ $filter->id }}" value="{{ request()->query('filter_' . $filter->id) }}">
                                    </div>
                                @elseif ($filter->type === 'checkbox')
                                    <div class="form-group col-md-12">
                                        <label>{{ $filter->label }}</label>
                                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                            @if ($filter->options_source === 'static' && !empty($filter->options))
                                                @foreach ($filter->options as $option)
                                                    @php
                                                        $selectedValues = request()->query('filter_' . $filter->id, []);
                                                        $isChecked = is_array($selectedValues) && in_array($option, $selectedValues);
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="filter_check_{{ $filter->id }}_{{ $loop->index }}"
                                                            name="filter_{{ $filter->id }}[]" value="{{ $option }}"
                                                            @if ($isChecked) checked @endif>
                                                        <label class="form-check-label"
                                                            for="filter_check_{{ $filter->id }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @elseif ($filter->options_source === 'dynamic')
                                                @php
                                                    try {
                                                        $checkboxOptions = \Illuminate\Support\Facades\DB::select($filter->options_query);
                                                    } catch (\Exception $e) {
                                                        $checkboxOptions = [];
                                                    }
                                                    $selectedValues = request()->query('filter_' . $filter->id, []);
                                                @endphp
                                                @foreach ($checkboxOptions as $option)
                                                    @php
                                                        $optionValue = $option->{$filter->options_column} ?? current((array) $option);
                                                        $isChecked = is_array($selectedValues) && in_array($optionValue, $selectedValues);
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="filter_check_{{ $filter->id }}_{{ $loop->index }}"
                                                            name="filter_{{ $filter->id }}[]" value="{{ $optionValue }}"
                                                            @if ($isChecked) checked @endif>
                                                        <label class="form-check-label"
                                                            for="filter_check_{{ $filter->id }}_{{ $loop->index }}">
                                                            {{ $optionValue }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($filter->type === 'multi_select')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter->id }}">{{ $filter->label }}</label>
                                        <select class="form-control" id="filter_{{ $filter->id }}" name="filter_{{ $filter->id }}[]" multiple>
                                            @if ($filter->options_source === 'static' && !empty($filter->options))
                                                @foreach ($filter->options as $option)
                                                    @php
                                                        $selectedValues = request()->query('filter_' . $filter->id, []);
                                                        $isSelected = is_array($selectedValues) && in_array($option, $selectedValues);
                                                    @endphp
                                                    <option value="{{ $option }}" @if ($isSelected) selected @endif>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            @elseif ($filter->options_source === 'dynamic')
                                                @php
                                                    try {
                                                        $selectOptions = \Illuminate\Support\Facades\DB::select($filter->options_query);
                                                    } catch (\Exception $e) {
                                                        $selectOptions = [];
                                                    }
                                                    $selectedValues = request()->query('filter_' . $filter->id, []);
                                                @endphp
                                                @foreach ($selectOptions as $option)
                                                    @php
                                                        $optionValue = $option->{$filter->options_column} ?? current((array) $option);
                                                        $isSelected = is_array($selectedValues) && in_array($optionValue, $selectedValues);
                                                    @endphp
                                                    <option value="{{ $optionValue }}" @if ($isSelected) selected @endif>
                                                        {{ $optionValue }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                @endif
                            @endforeach
                            <div class="form-group col-md-12 mt-4">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">Apply Filters</button>
                                    <button type="button" class="btn btn-secondary" onclick="clearDynamicFilters()">Clear Filters</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            @endif

            <table class="table text-center" id="content-table">
                <thead>
                    <tr>
                        @if (count($data) > 0)
                            @foreach ($data[0] as $attribute => $value)
                                <th>{{ ucfirst($attribute) }}</th>
                            @endforeach
                        @else
                            <th>No data available</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if (count($data) > 0)
                        @foreach ($data as $row)
                            <tr>
                                @foreach ($row as $value)
                                    <td>{{ $value }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div class="text-left"><a href="" id="downloadButton" class="btn btn-primary mb-3"
                    onclick="downloadTableAsExcel('{{ $name }}')">Download</a>
            </div>
        </div>
    </div>
    <script>
        let table = new DataTable('.table', {
            responsive: true
        });

        // Clear all filters and reload page without any filter parameters
        function clearFilters() {
            // Get the current URL without query parameters
            const baseUrl = window.location.pathname;
            window.location.href = baseUrl;
        }

        // Clear dynamic filters only
        function clearDynamicFilters() {
            // Get the current URL without query parameters that start with "filter_"
            const url = new URL(window.location);
            const params = new URLSearchParams(url.search);
            
            // Remove all filter_* parameters
            for (const [key] of params) {
                if (key.startsWith('filter_')) {
                    params.delete(key);
                }
            }
            
            url.search = params.toString();
            window.location.href = url.toString();
        }
    </script>
</body>

</html>