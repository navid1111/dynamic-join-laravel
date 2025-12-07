# Quick Reference: Dynamic Report Filters

## What Was Implemented

✅ **Dynamic Filter System** - Reports can have custom filters
✅ **Multiple Filter Types** - Dropdown, number_range, date_range, text
✅ **Conditional Display** - Filters only show if configured (no empty sections)
✅ **Query-based Options** - Dropdowns can pull options from database dynamically
✅ **Static Options** - Dropdowns can also have predefined options
✅ **Filter Integration** - Filters are applied to report queries automatically

## Files Changed/Created

1. **app/Models/Report.php** - Added filters support
2. **app/Http/Controllers/ReportController.php** - Added filter processing
3. **database/seeders/ReportSeeder.php** - Added filter configurations
4. **resources/views/viewReport/index.blade.php** - Added filter UI
5. **FILTER_IMPLEMENTATION_SUMMARY.md** - Documentation (created)

## How to Add Filters to a Report

When creating/updating a report in the seeder or admin panel, add a `filters` array:

```php
'filters' => json_encode([
    [
        'id' => 'subject_filter',
        'label' => 'Subject Name',
        'type' => 'dropdown',
        'table' => 'subjects',
        'column' => 'name',
        'options_source' => 'query',
        'options_query' => 'SELECT DISTINCT name FROM subjects',
        'default' => null,
        'required' => false,
        'order' => 1,
    ],
    [
        'id' => 'passing_marks',
        'label' => 'Passing Marks (Greater Than)',
        'type' => 'number_range',
        'table' => 'results',
        'column' => 'marks_obtained',
        'default' => null,
        'required' => false,
        'order' => 2,
    ],
])
```

## Current Reports with Filters

-   **Report 1**: Student Results Report - 1 filter (marks range)
-   **Report 2**: Subject Teachers Report - No filters
-   **Report 3**: Complete Results Analysis - 1 filter (minimum marks)
-   **Report 4**: Subjects Results Report - 2 filters (subject name, passing marks)

## Testing

1. Go to: `http://localhost:8000/view-reports/4`
2. You should see filter section with:
    - Subject Name dropdown
    - Passing Marks number input
3. Select values and click "Apply Filters"
4. Table data updates with filtered results

## Frontend Display Logic

```blade
@if (!empty($filters))
    <!-- Filters shown here -->
@endif
```

This ensures:

-   Reports WITHOUT filters don't show empty filter section
-   Reports WITH filters show appropriate filter controls
-   Each filter renders based on its type

## Filter Types and Rendering

| Type           | Input  | Example                   |
| -------------- | ------ | ------------------------- |
| `dropdown`     | SELECT | Status, Category, Subject |
| `number_range` | NUMBER | Age, Marks, Salary        |
| `date_range`   | DATE   | Date filter               |
| `text`         | TEXT   | Free text search          |

## Options Source Types

| Source   | Usage              | Example                     |
| -------- | ------------------ | --------------------------- |
| `static` | Predefined options | ["Active", "Inactive"]      |
| `query`  | Dynamic from DB    | "SELECT name FROM subjects" |

## API/Query Parameter Format

Apply filters via URL query parameters:

```
/view-reports/4?subject_filter=Math&passing_marks=75
```

Multiple filters are AND-ed together in the WHERE clause.
