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
            <!-- REMOVED - Using only Dynamic Filters -->

            <!-- Old Inline Filters -->
            <!-- REMOVED - Using only Dynamic Filters -->

            <!-- New FilterDefinition Filters -->
            @if (!empty($newFilters) && count($newFilters) > 0)
                <div class="bg-white p-4 rounded-md mt-4 border border-blue-300">
                    <h5 class="mb-3">
                        <span class="badge badge-info">NEW</span> Dynamic Filters ({{ count($newFilters) }} filters)
                    </h5>
                    <form id="dynamicFilterForm" method="GET">
                        <div class="form-row">
                            @foreach ($newFilters as $filter)
                                @if ($filter->is_conditional && !empty($filter->conditional_targets))
                                    {{-- Conditional Filter --}}
                                    <div class="form-group col-md-8 col-sm-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <label class="font-weight-bold mb-3">{{ $filter->label }}</label>
                                                
                                                {{-- Conditional Target Selector --}}
                                                @if ($filter->conditional_type === 'button_group')
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block mb-2">Select what to filter:</small>
                                                        <div class="btn-group btn-group-toggle" role="group">
                                                            @foreach ($filter->conditional_targets as $index => $target)
                                                                @php
                                                                    $isSelected = request()->query('filter_' . $filter->id . '_target') === $target['key'] || 
                                                                                  (!request()->query('filter_' . $filter->id . '_target') && $index === 0);
                                                                @endphp
                                                                <input type="radio" class="btn-check" name="filter_{{ $filter->id }}_target" 
                                                                    id="filter_{{ $filter->id }}_target_{{ $target['key'] }}" 
                                                                    value="{{ $target['key'] }}" 
                                                                    {{ $isSelected ? 'checked' : '' }}
                                                                    autocomplete="off">
                                                                <label class="btn btn-sm btn-outline-primary" for="filter_{{ $filter->id }}_target_{{ $target['key'] }}">
                                                                    {{ $target['label'] }}
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @elseif ($filter->conditional_type === 'tabs')
                                                    <div class="mb-3">
                                                        <small class="text-muted d-block mb-2">Select what to filter:</small>
                                                        <ul class="nav nav-pills" role="tablist">
                                                            @foreach ($filter->conditional_targets as $index => $target)
                                                                <li class="nav-item">
                                                                    <a class="nav-link {{ ($index === 0 && !request()->query('filter_' . $filter->id . '_target')) || (request()->query('filter_' . $filter->id . '_target') === $target['key']) ? 'active' : '' }}" 
                                                                        href="#" onclick="selectConditionalTarget(event, {{ $filter->id }}, '{{ $target['key'] }}')">
                                                                        {{ $target['label'] }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                        <input type="hidden" name="filter_{{ $filter->id }}_target" id="filter_{{ $filter->id }}_target" 
                                                            value="{{ request()->query('filter_' . $filter->id . '_target') ?? $filter->conditional_targets[0]['key'] }}">
                                                    </div>
                                                @elseif ($filter->conditional_type === 'dropdown')
                                                    <div class="mb-3">
                                                        <label for="filter_{{ $filter->id }}_target" class="small text-muted">Select what to filter:</label>
                                                        <select class="form-control" name="filter_{{ $filter->id }}_target" id="filter_{{ $filter->id }}_target">
                                                            @foreach ($filter->conditional_targets as $index => $target)
                                                                <option value="{{ $target['key'] }}" {{ (($index === 0 && !request()->query('filter_' . $filter->id . '_target')) || (request()->query('filter_' . $filter->id . '_target') === $target['key'])) ? 'selected' : '' }}>
                                                                    {{ $target['label'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                
                                                {{-- Filter Value Input Based on Type --}}
                                                <div>
                                                    <small class="text-muted d-block mb-2">Enter filter value:</small>
                                                    @if ($filter->type === 'date_range')
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <input type="date" class="form-control" name="filter_{{ $filter->id }}_min" placeholder="Start Date"
                                                                    value="{{ request()->query('filter_' . $filter->id . '_min') }}">
                                                            </div>
                                                            <div class="col-6">
                                                                <input type="date" class="form-control" name="filter_{{ $filter->id }}_max" placeholder="End Date"
                                                                    value="{{ request()->query('filter_' . $filter->id . '_max') }}">
                                                            </div>
                                                        </div>
                                                    @elseif ($filter->type === 'date')
                                                        <input type="date" class="form-control" name="filter_{{ $filter->id }}"
                                                            value="{{ request()->query('filter_' . $filter->id) }}">
                                                    @elseif ($filter->type === 'number_range')
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <input type="number" class="form-control" name="filter_{{ $filter->id }}_min" placeholder="Min"
                                                                    value="{{ request()->query('filter_' . $filter->id . '_min') }}">
                                                            </div>
                                                            <div class="col-6">
                                                                <input type="number" class="form-control" name="filter_{{ $filter->id }}_max" placeholder="Max"
                                                                    value="{{ request()->query('filter_' . $filter->id . '_max') }}">
                                                            </div>
                                                        </div>
                                                    @elseif ($filter->type === 'text')
                                                        <input type="text" class="form-control" name="filter_{{ $filter->id }}" placeholder="{{ $filter->placeholder ?? 'Search...' }}"
                                                            value="{{ request()->query('filter_' . $filter->id) }}">
                                                    @elseif ($filter->type === 'number')
                                                        <input type="number" class="form-control" name="filter_{{ $filter->id }}" placeholder="{{ $filter->placeholder ?? 'Enter number' }}"
                                                            value="{{ request()->query('filter_' . $filter->id) }}">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($filter->type === 'dropdown')
                                    <div class="form-group col-lg-3 col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter->id }}">{{ $filter->label }}</label>
                                        <select class="form-control" id="filter_{{ $filter->id }}" name="filter_{{ $filter->id }}">
                                            <option value="">-- Select --</option>
                                            @if ($filter->options_source === 'static' && !empty($filter->options))
                                                @foreach ($filter->options as $key => $value)
                                                    <option value="{{ $key }}"
                                                        @if (request()->query('filter_' . $filter->id) === $key) selected @endif>
                                                        {{ $value }}
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
                                @elseif ($filter->type === 'number_range')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label>{{ $filter->label }}</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="number" class="form-control" id="filter_{{ $filter->id }}_min"
                                                    name="filter_{{ $filter->id }}_min" placeholder="Min"
                                                    value="{{ request()->query('filter_' . $filter->id . '_min') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control" id="filter_{{ $filter->id }}_max"
                                                    name="filter_{{ $filter->id }}_max" placeholder="Max"
                                                    value="{{ request()->query('filter_' . $filter->id . '_max') }}">
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($filter->type === 'date_range')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label>{{ $filter->label }}</label>
                                        <div class="row">
                                            <div class="col-6">
                                                <input type="date" class="form-control" id="filter_{{ $filter->id }}_min"
                                                    name="filter_{{ $filter->id }}_min"
                                                    value="{{ request()->query('filter_' . $filter->id . '_min') }}">
                                            </div>
                                            <div class="col-6">
                                                <input type="date" class="form-control" id="filter_{{ $filter->id }}_max"
                                                    name="filter_{{ $filter->id }}_max"
                                                    value="{{ request()->query('filter_' . $filter->id . '_max') }}">
                                            </div>
                                        </div>
                                    </div>
                                @elseif ($filter->type === 'autocomplete')
                                    <div class="form-group col-md-4 col-sm-6">
                                        <label for="filter_{{ $filter->id }}">{{ $filter->label }}</label>
                                        <input type="text" class="form-control" id="filter_{{ $filter->id }}"
                                            name="filter_{{ $filter->id }}" placeholder="{{ $filter->placeholder ?? 'Search...' }}"
                                            value="{{ request()->query('filter_' . $filter->id) }}" list="autocomplete_{{ $filter->id }}">
                                        @if ($filter->options_source === 'dynamic')
                                            @php
                                                try {
                                                    $autocompleteOptions = \Illuminate\Support\Facades\DB::select($filter->options_query);
                                                } catch (\Exception $e) {
                                                    $autocompleteOptions = [];
                                                }
                                            @endphp
                                            <datalist id="autocomplete_{{ $filter->id }}">
                                                @foreach ($autocompleteOptions as $option)
                                                    @php
                                                        $optionValue = $option->{$filter->options_column} ?? current((array) $option);
                                                    @endphp
                                                    <option value="{{ $optionValue }}">
                                                @endforeach
                                            </datalist>
                                        @endif
                                    </div>
                                @elseif ($filter->type === 'radio')
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label>{{ $filter->label }}</label>
                                        <div class="border p-2 rounded">
                                            @if ($filter->options_source === 'static' && !empty($filter->options))
                                                @foreach ($filter->options as $key => $value)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            id="filter_radio_{{ $filter->id }}_{{ $loop->index }}"
                                                            name="filter_{{ $filter->id }}" value="{{ $key }}"
                                                            @if (request()->query('filter_' . $filter->id) === $key) checked @endif>
                                                        <label class="form-check-label"
                                                            for="filter_radio_{{ $filter->id }}_{{ $loop->index }}">
                                                            {{ $value }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @elseif ($filter->options_source === 'dynamic')
                                                @php
                                                    try {
                                                        $radioOptions = \Illuminate\Support\Facades\DB::select($filter->options_query);
                                                    } catch (\Exception $e) {
                                                        $radioOptions = [];
                                                    }
                                                @endphp
                                                @foreach ($radioOptions as $option)
                                                    @php
                                                        $optionValue = $option->{$filter->options_column} ?? current((array) $option);
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio"
                                                            id="filter_radio_{{ $filter->id }}_{{ $loop->index }}"
                                                            name="filter_{{ $filter->id }}" value="{{ $optionValue }}"
                                                            @if (request()->query('filter_' . $filter->id) === $optionValue) checked @endif>
                                                        <label class="form-check-label"
                                                            for="filter_radio_{{ $filter->id }}_{{ $loop->index }}">
                                                            {{ $optionValue }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>{{ $filter->label }}</label>
                                        <div class="border p-3 rounded" style="max-height: 150px; overflow-y: auto;">
                                            @if ($filter->options_source === 'static' && !empty($filter->options))
                                                @foreach ($filter->options as $key => $value)
                                                    @php
                                                        $selectedValues = request()->query('filter_' . $filter->id, []);
                                                        $isChecked = is_array($selectedValues) && in_array($key, $selectedValues);
                                                    @endphp
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="filter_check_{{ $filter->id }}_{{ $loop->index }}"
                                                            name="filter_{{ $filter->id }}[]" value="{{ $key }}"
                                                            @if ($isChecked) checked @endif>
                                                        <label class="form-check-label"
                                                            for="filter_check_{{ $filter->id }}_{{ $loop->index }}">
                                                            {{ $value }}
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
                                                @foreach ($filter->options as $key => $value)
                                                    @php
                                                        $selectedValues = request()->query('filter_' . $filter->id, []);
                                                        $isSelected = is_array($selectedValues) && in_array($key, $selectedValues);
                                                    @endphp
                                                    <option value="{{ $key }}" @if ($isSelected) selected @endif>
                                                        {{ $value }}
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

        // Clear dynamic filters only (removes all filter_* parameters)
        function clearDynamicFilters() {
            const url = new URL(window.location);
            const params = new URLSearchParams(url.search);
            
            // Collect keys to delete (avoid modifying while iterating)
            const keysToDelete = [];
            for (const [key] of params) {
                if (key.startsWith('filter_')) {
                    keysToDelete.push(key);
                }
            }
            
            // Delete all collected filter keys
            keysToDelete.forEach(key => params.delete(key));
            
            // Reconstruct URL
            url.search = params.toString();
            window.location.href = url.toString();
        }

        // Select conditional target for tab-based filters
        function selectConditionalTarget(event, filterId, targetKey) {
            event.preventDefault();
            
            // Update hidden input
            document.getElementById('filter_' + filterId + '_target').value = targetKey;
            
            // Update active tab
            const tabs = event.target.closest('.nav-pills').querySelectorAll('.nav-link');
            tabs.forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');
        }
    </script>
</body>

</html>