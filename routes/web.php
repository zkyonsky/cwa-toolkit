<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    //route users
    Route::middleware(['permission:create-users'])->group(function () {
        Route::post('users/import-csv', [\App\Http\Controllers\UserController::class, 'importCsv'])->name('users.import-csv');
        Route::get('users/download-template', [\App\Http\Controllers\UserController::class, 'downloadTemplate'])->name('users.download-template');
        Route::resource('users', \App\Http\Controllers\UserController::class)->only(['create', 'store']);
    });
    Route::middleware(['permission:view-users'])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->only(['index', 'show']);
    });
    Route::middleware(['permission:edit-users'])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->only(['edit', 'update']);
    });
    Route::middleware(['permission:delete-users'])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->only(['destroy']);
    });

    //route roles
    Route::middleware(['permission:create-roles'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->only(['create', 'store']);
    });
    Route::middleware(['permission:view-roles'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->only(['index', 'show']);
    });
    Route::middleware(['permission:edit-roles'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->only(['edit', 'update']);
    });
    Route::middleware(['permission:delete-roles'])->group(function () {
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->only(['destroy']);
    });

    //route permissions
    Route::middleware(['permission:view-permissions'])->group(function () {
        Route::get('/permissions', [\App\Http\Controllers\PermissionController::class, 'index'])->name('permissions.index');
    });

    //route govs
    Route::middleware(['permission:create-govs'])->group(function () {
        Route::resource('govs', \App\Http\Controllers\GovController::class)->only(['create', 'store']);
    });
    Route::middleware(['permission:view-govs'])->group(function () {
        Route::resource('govs', \App\Http\Controllers\GovController::class)->only(['index', 'show']);
    });
    Route::middleware(['permission:edit-govs'])->group(function () {
        Route::resource('govs', \App\Http\Controllers\GovController::class)->only(['edit', 'update']);
    });
    Route::middleware(['permission:delete-govs'])->group(function () {
        Route::resource('govs', \App\Http\Controllers\GovController::class)->only(['destroy']);
    });

    //route assessees
    Route::middleware(['permission:create-assessees'])->group(function () {
        Route::resource('assessees', \App\Http\Controllers\AssesseeController::class)->only(['create', 'store']);
    });
    Route::middleware(['permission:view-assessees'])->group(function () {
        Route::resource('assessees', \App\Http\Controllers\AssesseeController::class)->only(['index', 'show']);
    });
    Route::middleware(['permission:edit-assessees'])->group(function () {
        Route::resource('assessees', \App\Http\Controllers\AssesseeController::class)->only(['edit', 'update']);
    });
    Route::middleware(['permission:delete-assessees'])->group(function () {
        Route::resource('assessees', \App\Http\Controllers\AssesseeController::class)->only(['destroy']);
    });

    //route assessments
    Route::middleware(['permission:create-assessments'])->group(function () {
        Route::resource('assessments', \App\Http\Controllers\AssessmentController::class)->only(['create', 'store']);
    });
    Route::middleware(['permission:view-assessments'])->group(function () {
        Route::resource('assessments', \App\Http\Controllers\AssessmentController::class)->only(['index', 'show']);
    });
    Route::middleware(['permission:edit-assessments'])->group(function () {
        Route::resource('assessments', \App\Http\Controllers\AssessmentController::class)->only(['edit', 'update']);
    });
    Route::middleware(['permission:delete-assessments'])->group(function () {
        Route::resource('assessments', \App\Http\Controllers\AssessmentController::class)->only(['destroy']);
    });

    //route manual
    Route::inertia('manual', 'Manual/Index')->name('manual');

    //route upload data
    Route::middleware(['permission:view-users'])->group(function () {
        Route::get('upload-data', [\App\Http\Controllers\UploadDataController::class, 'index'])->name('upload-data.index');
        Route::post('upload-data/{type}', [\App\Http\Controllers\UploadDataController::class, 'upload'])->middleware('throttle:10,1')->name('upload-data.upload');
        Route::get('upload-data/template/{type}', [\App\Http\Controllers\UploadDataController::class, 'downloadTemplate'])->name('upload-data.template');
    });

    //route assessment details
    Route::middleware(['permission:edit-assessments'])->group(function () {
        Route::get('assessment-details/{assessment}/infrastructure', [\App\Http\Controllers\AssessmentDetail\InfrastructureController::class, 'edit'])->name('assessment-details.infrastructure.edit');
        Route::put('assessment-details/{assessment}/infrastructure', [\App\Http\Controllers\AssessmentDetail\InfrastructureController::class, 'update'])->name('assessment-details.infrastructure.update');

        Route::get('assessment-details/{assessment}/budget-real', [\App\Http\Controllers\AssessmentDetail\BudgetRealController::class, 'edit'])->name('assessment-details.budget-real.edit');
        Route::put('assessment-details/{assessment}/budget-real', [\App\Http\Controllers\AssessmentDetail\BudgetRealController::class, 'update'])->name('assessment-details.budget-real.update');
        Route::delete('assessment-details/{assessment}/budget-real', [\App\Http\Controllers\AssessmentDetail\BudgetRealController::class, 'destroy'])->name('assessment-details.budget-real.destroy')->middleware('permission:delete-budget_reals');

        Route::get('assessment-details/{assessment}/economy-condition', [\App\Http\Controllers\AssessmentDetail\EconomyConditionController::class, 'edit'])->name('assessment-details.economy-condition.edit');
        Route::put('assessment-details/{assessment}/economy-condition', [\App\Http\Controllers\AssessmentDetail\EconomyConditionController::class, 'update'])->name('assessment-details.economy-condition.update');
        Route::delete('assessment-details/{assessment}/economy-condition', [\App\Http\Controllers\AssessmentDetail\EconomyConditionController::class, 'destroy'])->name('assessment-details.economy-condition.destroy')->middleware('permission:delete-economy_indicators');

        Route::get('assessment-details/{assessment}/financial-condition', [\App\Http\Controllers\AssessmentDetail\FinancialConditionController::class, 'edit'])->name('assessment-details.financial-condition.edit');
        Route::put('assessment-details/{assessment}/financial-condition', [\App\Http\Controllers\AssessmentDetail\FinancialConditionController::class, 'update'])->name('assessment-details.financial-condition.update');

        Route::get('assessment-details/{assessment}/debt-service', [\App\Http\Controllers\AssessmentDetail\DebtServiceController::class, 'edit'])->name('assessment-details.debt-service.edit');
        Route::put('assessment-details/{assessment}/debt-service', [\App\Http\Controllers\AssessmentDetail\DebtServiceController::class, 'update'])->name('assessment-details.debt-service.update');

        Route::get('assessment-details/{assessment}/dscr', [\App\Http\Controllers\AssessmentDetail\DscrController::class, 'edit'])->name('assessment-details.dscr.edit');
        Route::put('assessment-details/{assessment}/dscr', [\App\Http\Controllers\AssessmentDetail\DscrController::class, 'update'])->name('assessment-details.dscr.update');
        Route::delete('assessment-details/{assessment}/dscr/budget-plan', [\App\Http\Controllers\AssessmentDetail\DscrController::class, 'destroyBudgetPlan'])->name('assessment-details.dscr.destroy-budget-plan')->middleware('permission:delete-budget_plans');

        Route::get('assessment-details/{assessment}/indicative-rating', [\App\Http\Controllers\AssessmentDetail\IndicativeRatingController::class, 'edit'])->name('assessment-details.indicative-rating.edit');
        Route::put('assessment-details/{assessment}/indicative-rating', [\App\Http\Controllers\AssessmentDetail\IndicativeRatingController::class, 'update'])->name('assessment-details.indicative-rating.update');

        Route::get('assessment-details/{assessment}/action-plan', [\App\Http\Controllers\AssessmentDetail\ActionPlanController::class, 'edit'])->name('assessment-details.action-plan.edit');
        Route::put('assessment-details/{assessment}/action-plan', [\App\Http\Controllers\AssessmentDetail\ActionPlanController::class, 'update'])->name('assessment-details.action-plan.update');
        Route::get('assessment-details/{assessment}/report', [\App\Http\Controllers\AssessmentDetail\ReportController::class, 'show'])->name('assessment-details.report.show');
    });

});

require __DIR__ . '/settings.php';
