<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filter Preview - {{ $filterDefinition->label }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Filter Preview</h1>
                <p class="text-gray-600 mt-2">{{ $filterDefinition->label }}</p>
            </div>
            <a href="{{ route('filters.show', $filterDefinition) }}"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Filter
            </a>
        </div>

        <div class="grid grid-cols-2 gap-6">
            <!-- Filter Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Filter Information</h2>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-gray-600 font-semibold text-sm">Name</dt>
                        <dd class="text-gray-800">{{ $filterDefinition->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 font-semibold text-sm">Type</dt>
                        <dd>
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst(str_replace('_', ' ', $filterDefinition->type)) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 font-semibold text-sm">Target Table</dt>
                        <dd class="text-gray-800">{{ $filterDefinition->target_table }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 font-semibold text-sm">Target Column</dt>
                        <dd class="text-gray-800">{{ $filterDefinition->target_column }}</dd>
                    </div>
                    @if ($filterDefinition->options_source !== 'none')
                        <div>
                            <dt class="text-gray-600 font-semibold text-sm">Options Source</dt>
                            <dd class="text-gray-800">{{ ucfirst($filterDefinition->options_source) }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-600 font-semibold text-sm">Required</dt>
                        <dd class="text-gray-800">{{ $filterDefinition->required ? 'Yes' : 'No' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Live Preview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">How it will look in Reports</h2>

                <div class="border border-gray-300 rounded p-4 bg-gray-50">
                    <!-- Render based on filter type -->
                    @if ($filterDefinition->type === 'text')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <input type="text" placeholder="{{ $filterDefinition->placeholder }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                disabled>
                        </div>

                    @elseif ($filterDefinition->type === 'number')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <input type="number" placeholder="{{ $filterDefinition->placeholder }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                disabled>
                        </div>

                    @elseif ($filterDefinition->type === 'number_range')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" placeholder="From"
                                    class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    disabled>
                                <input type="number" placeholder="To"
                                    class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    disabled>
                            </div>
                        </div>

                    @elseif ($filterDefinition->type === 'date')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <input type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                disabled>
                        </div>

                    @elseif ($filterDefinition->type === 'date_range')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date"
                                    class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    disabled>
                                <input type="date"
                                    class="px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                    disabled>
                            </div>
                        </div>

                    @elseif ($filterDefinition->type === 'dropdown')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <select
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                disabled>
                                <option value="">{{ $filterDefinition->placeholder ?? '-- Select --' }}</option>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                    @elseif ($filterDefinition->type === 'radio')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-3">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <div class="space-y-2">
                                @foreach ($options as $option)
                                    <div class="flex items-center">
                                        <input type="radio" id="radio_{{ md5($option) }}" name="filter_radio" class="mr-2"
                                            disabled>
                                        <label for="radio_{{ md5($option) }}" class="text-gray-700">{{ $option }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    @elseif ($filterDefinition->type === 'checkbox')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-3">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <div class="space-y-2">
                                @foreach ($options as $option)
                                    <div class="flex items-center">
                                        <input type="checkbox" id="check_{{ md5($option) }}" class="mr-2" disabled>
                                        <label for="check_{{ md5($option) }}" class="text-gray-700">{{ $option }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    @elseif ($filterDefinition->type === 'multi_select')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <select multiple
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                disabled>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            <p class="text-gray-500 text-xs mt-1">Hold Ctrl/Cmd to select multiple</p>
                        </div>

                    @elseif ($filterDefinition->type === 'autocomplete')
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                {{ $filterDefinition->label }}
                                @if ($filterDefinition->required) <span class="text-red-600">*</span> @endif
                            </label>
                            <input type="text" placeholder="{{ $filterDefinition->placeholder ?? 'Search...' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                                disabled>
                            <p class="text-gray-500 text-xs mt-1">Autocomplete field ({{ count($options) }} options
                                available)</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Options Details -->
        @if ($filterDefinition->options_source !== 'none' && !empty($options))
            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    Available Options
                    <span class="text-sm font-normal text-gray-600">({{ count($options) }} total)</span>
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Options List</h3>
                        <div class="bg-gray-50 rounded p-3 max-h-48 overflow-y-auto">
                            <ul class="space-y-1">
                                @foreach ($options as $index => $option)
                                    <li class="text-sm text-gray-800">{{ $index + 1 }}. {{ $option }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700 mb-2">Options Details</h3>
                        <div class="bg-blue-50 rounded p-3 border border-blue-200">
                            @if ($filterDefinition->options_source === 'dynamic')
                                <p class="text-sm text-gray-700 mb-2">
                                    <strong>Source:</strong> Database
                                </p>
                                <p class="text-sm text-gray-700 mb-2">
                                    <strong>Query:</strong>
                                </p>
                                <pre
                                    class="text-xs bg-gray-100 p-2 rounded overflow-x-auto"><code>{{ $filterDefinition->options_query }}</code></pre>
                            @else
                                <p class="text-sm text-gray-700">
                                    <strong>Source:</strong> Static (manually entered)
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Description -->
        @if ($filterDefinition->description)
            <div class="mt-6 bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Description</h2>
                <p class="text-gray-700">{{ $filterDefinition->description }}</p>
            </div>
        @endif
    </div>
</body>

</html>