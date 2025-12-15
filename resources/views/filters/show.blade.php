<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Filter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">{{ $filterDefinition->label }}</h1>
            <div class="space-x-2">
                <a href="{{ route('filters.preview', $filterDefinition) }}"
                    class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Preview
                </a>
                <a href="{{ route('filters.edit', $filterDefinition) }}"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <a href="{{ route('filters.assignToReports', $filterDefinition) }}"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Assign to Reports
                </a>
                <a href="{{ route('filters.index') }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
            </div>
        </div>

        @if ($message = session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ $message }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Filter Details</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-gray-600 font-semibold">Name</dt>
                        <dd class="text-gray-800">{{ $filterDefinition->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 font-semibold">Label</dt>
                        <dd class="text-gray-800">{{ $filterDefinition->label }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 font-semibold">Type</dt>
                        <dd>
                            <span
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst(str_replace('_', ' ', $filterDefinition->type)) }}
                            </span>
                        </dd>
                    </div>
                    @if($filterDefinition->is_conditional && !empty($filterDefinition->conditional_targets))
                        <div>
                            <dt class="text-gray-600 font-semibold">Conditional Type</dt>
                            <dd>
                                <span
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                    {{ ucfirst(str_replace('_', ' ', $filterDefinition->conditional_type)) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-600 font-semibold text-sm">Conditional Targets</dt>
                            <dd class="text-gray-800">
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    @foreach($filterDefinition->conditional_targets as $target)
                                        <li class="text-sm">
                                            <strong>{{ $target['label'] }}</strong>:
                                            {{ $target['table'] }}.{{ $target['column'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @else
                        <div>
                            <dt class="text-gray-600 font-semibold">Target Table</dt>
                            <dd class="text-gray-800">{{ $filterDefinition->target_table }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-600 font-semibold">Target Column</dt>
                            <dd class="text-gray-800">{{ $filterDefinition->target_column }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-600 font-semibold">Status</dt>
                        <dd>
                            @if ($filterDefinition->is_active)
                                <span
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Active
                                </span>
                            @else
                                <span
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-600 font-semibold">Required</dt>
                        <dd class="text-gray-800">{{ $filterDefinition->required ? 'Yes' : 'No' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Additional Info</h2>
                <dl class="space-y-4">
                    @if ($filterDefinition->placeholder)
                        <div>
                            <dt class="text-gray-600 font-semibold">Placeholder</dt>
                            <dd class="text-gray-800">{{ $filterDefinition->placeholder }}</dd>
                        </div>
                    @endif

                    @if ($filterDefinition->options_source !== 'none')
                        <div>
                            <dt class="text-gray-600 font-semibold">Options Source</dt>
                            <dd class="text-gray-800">{{ ucfirst($filterDefinition->options_source) }}</dd>
                        </div>
                    @endif

                    @if ($filterDefinition->options_source === 'static' && $filterDefinition->options)
                        <div>
                            <dt class="text-gray-600 font-semibold">Static Options</dt>
                            <dd>
                                <ul class="list-disc list-inside text-gray-800">
                                    @foreach (is_array($filterDefinition->options) ? $filterDefinition->options : json_decode($filterDefinition->options, true) as $option)
                                        <li>{{ $option }}</li>
                                    @endforeach
                                </ul>
                            </dd>
                        </div>
                    @endif

                    @if ($filterDefinition->options_source === 'query' && $filterDefinition->options_query)
                        <div>
                            <dt class="text-gray-600 font-semibold">Query</dt>
                            <dd class="text-gray-800 font-mono text-sm bg-gray-50 p-2 rounded">
                                {{ $filterDefinition->options_query }}
                            </dd>
                        </div>
                    @endif

                    @if ($filterDefinition->description)
                        <div>
                            <dt class="text-gray-600 font-semibold">Description</dt>
                            <dd class="text-gray-800">{{ $filterDefinition->description }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Assigned Reports</h2>

            @if ($filterDefinition->reports()->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($filterDefinition->reports as $report)
                        <div class="border border-gray-300 rounded p-4">
                            <h3 class="font-semibold text-gray-800">{{ $report->report_name }}</h3>
                            <p class="text-gray-600 text-sm">Table: {{ $report->report_details['table'] ?? 'Unknown' }}</p>
                            <a href="{{ route('viewReport.index', $report->id) }}"
                                class="text-blue-600 hover:text-blue-900 text-sm mt-2 inline-block">
                                View Report →
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">This filter is not yet assigned to any reports.</p>
                <a href="{{ route('filters.assignToReports', $filterDefinition) }}"
                    class="text-blue-600 hover:text-blue-900 mt-4 inline-block">
                    Assign to Reports →
                </a>
            @endif
        </div>
    </div>
</body>

</html>