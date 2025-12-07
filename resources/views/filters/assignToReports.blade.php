<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Filter</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-3xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Assign Filter: {{ $filterDefinition->label }}</h1>

        @if ($message = session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ $message }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('filters.updateAssignments', $filterDefinition) }}" method="POST"
            class="bg-white rounded-lg shadow p-6">
            @csrf

            <div class="mb-4">
                <p class="text-gray-700 mb-4">
                    Select the reports you want to assign this filter to. Only reports with compatible tables and
                    columns are shown.
                </p>

                @if (!empty($compatibleReports))
                    <div class="space-y-3">
                        @foreach ($compatibleReports as $report)
                            <div class="flex items-start p-4 border border-gray-200 rounded hover:bg-gray-50">
                                <input type="checkbox" id="report_{{ $report['id'] }}" name="report_ids[]"
                                    value="{{ $report['id'] }}" {{ $report['is_assigned'] ? 'checked' : '' }} class="mt-1 mr-4">
                                <label for="report_{{ $report['id'] }}" class="cursor-pointer flex-grow">
                                    <div class="font-semibold text-gray-800">{{ $report['name'] }}</div>
                                    <div class="text-sm text-gray-600">Table: {{ $report['table'] }}</div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                        No compatible reports found for this filter. The table "{{ $filterDefinition->target_table }}" must
                        exist in the report configuration.
                    </div>
                @endif
            </div>

            <div class="flex justify-between mt-6">
                <a href="{{ route('filters.show', $filterDefinition) }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Save Assignments
                </button>
            </div>
        </form>
    </div>
</body>

</html>