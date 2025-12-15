<?php

use App\Http\Controllers\FilterManagementController;
// use App\Http\Controllers\ProductController;
// use App\Http\Controllers\CustomerController;
// use App\Http\Controllers\DynamicDependentController;
// use App\Http\Controllers\Automatic;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ViewReportListController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/product', [ProductController::class, 'index'])->name('product.index');
// Route::get('/product/create', [ProductController::class, 'create'])->name('product.create');
// Route::post('/product', [ProductController::class, 'store'])->name('product.store');

// Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
// Route::get('/customer/create', [CustomerController::class, 'create'])->name('customer.create');
// Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');

// Route::get('/dynamicview', [DynamicDependentController::class, 'index'])->name('dynamicInfoViewer.index');
// Route::post('/dynamicview/fetch', [DynamicDependentController::class, 'fetch'])->name('dynamicInfoViewer.fetch');
// Route::post('/dynamicview/fetch_datas', [DynamicDependentController::class, 'fetch_datas'])->name('dynamicInfoViewer.fetch_datas');

// Route::get('/automatic', [Automatic::class, 'index'])->name('automatic.index');
// Route::post('/automatic/fetch', [Automatic::class, 'fetch'])->name('automatic.fetch');
// Route::post('/automatic/fetch_datas', [Automatic::class, 'fetch_datas'])->name('automatic.fetch_datas');

Route::get('/create-report', [JoinController::class, 'index'])->name('adminViewCreate.index');
Route::post('/create-report/fetch', [JoinController::class, 'fetch'])->name('adminViewCreate.fetch');
Route::post('/create-report/fetch_datas', [JoinController::class, 'fetch_datas'])->name('adminViewCreate.fetch_datas');
Route::post('/create-report/fetch_join_datas', [JoinController::class, 'fetch_join_datas'])->name('adminViewCreate.fetch_join_datas');
Route::post('/create-report', [JoinController::class, 'processForm'])->name('adminViewCreate.processForm');

// Route::Resource('/view-report', ReportController::class);
Route::get('/view-reports/{id}/{startDate?}/{endDate?}', [ReportController::class, 'showData'])->name('viewReport.index');
Route::get('/view-report/{id}/delete', [ReportController::class, 'destroy']);
Route::get('/view-report/{id}/edit', [ReportController::class, 'edit'])->name('adminViewCreate.edit');
Route::post('/view-report/{id}/edit', [ReportController::class, 'editForm'])->name('adminViewCreate.editForm');
Route::post('/view-report/edit/fetch', [JoinController::class, 'fetch'])->name('adminViewCreate.fetch');
Route::prefix('filters')->name('filters.')->group(function () {
    Route::get('/', [FilterManagementController::class, 'index'])->name('index');
    Route::get('/create', [FilterManagementController::class, 'create'])->name('create');
    Route::post('/', [FilterManagementController::class, 'store'])->name('store');
    Route::get('/api/table-columns', [FilterManagementController::class, 'getTableColumns'])->name('getTableColumns');
    Route::get('/{filterDefinition}/preview', [FilterManagementController::class, 'preview'])->name('preview');
    Route::get('/{filterDefinition}', [FilterManagementController::class, 'show'])->name('show');
    Route::get('/{filterDefinition}/edit', [FilterManagementController::class, 'edit'])->name('edit');
    Route::put('/{filterDefinition}', [FilterManagementController::class, 'update'])->name('update');
    Route::delete('/{filterDefinition}', [FilterManagementController::class, 'destroy'])->name('destroy');
    Route::get('/{filterDefinition}/assign', [FilterManagementController::class, 'assignToReports'])->name('assignToReports');
    Route::post('/{filterDefinition}/assign', [FilterManagementController::class, 'updateAssignments'])->name('updateAssignments');
});
Route::get('/view-report-list', [ViewReportListController::class, 'index']);

use App\Http\Controllers\ReportTransformationController;

Route::middleware(['web'])->group(function () {
    // Transformation routes
    Route::get('/reports/{report}/transformations', [ReportTransformationController::class, 'configure'])
        ->name('reports.transformations.configure');
    Route::post('/reports/{report}/transformations', [ReportTransformationController::class, 'store'])
        ->name('reports.transformations.store');
    Route::post('/reports/transformations/preview', [ReportTransformationController::class, 'preview'])
        ->name('reports.transformations.preview');
    Route::get('/reports/transformations/config', [ReportTransformationController::class, 'getTransformerConfig'])
        ->name('reports.transformations.config');
});
