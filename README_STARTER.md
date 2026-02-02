# Dynamic Join Laravel: Starter Guide

Welcome! This guide will help you get started with the Dynamic Join Laravel package, focusing on dynamic report filters.

## 1. Installation

- Clone the repository
- Run `composer install`
- Set up your `.env` and database
- Run migrations: `php artisan migrate`
- (Optional) Seed reports: `php artisan db:seed --class=ReportSeeder`

## 2. Key Features

- Dynamic filters for reports (dropdown, number range, date range, text)
- Filters are defined per report (see `database/seeders/ReportSeeder.php`)
- Filters are shown only if configured
- Dropdowns can use static or DB-driven options
- Filters are applied to report queries automatically

## 3. Usage

- Visit `/view-reports` to see all reports
- Click a report to view its filters and data
- Select filter values and click "Apply Filters" to update results
- API endpoints available for AJAX filter/data loading

## 4. Adding/Editing Filters

- Edit `filters` array in `ReportSeeder.php` or via admin panel
- Example filter config:

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

## 5. Testing

- Go to `/view-reports/{id}` (e.g., `/view-reports/4`)
- Use the filter UI and verify table updates

## 6. Reference

- See `FILTER_QUICK_REFERENCE.md` for filter types and usage
- See `FUNCTIONS_AND_ROUTES.mdd` for function/route reference

---

For questions, see the code comments or contact the maintainer.
