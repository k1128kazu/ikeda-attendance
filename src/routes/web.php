<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminCorrectionController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| 一般ユーザーは Fortify を使用
| 管理者は /admin プレフィックスで分離
|
*/

/*
|--------------------------------------------------------------------------
| 初期導線
|--------------------------------------------------------------------------
*/

// トップアクセス時は会員登録画面へ
Route::get('/', function () {
    return redirect('/register');
});


/*
|--------------------------------------------------------------------------
| 一般ユーザー（auth 必須）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // 勤怠打刻画面
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn']);
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut']);

    // 勤怠一覧（月次）
    Route::get('/attendance/list', [AttendanceController::class, 'list']);

    // 勤怠詳細
    Route::get('/attendance/{id}', [AttendanceController::class, 'show']);

    // 修正申請
    Route::get('/attendance/{id}/edit', [AttendanceCorrectionController::class, 'create']);
    Route::post('/attendance/{id}/edit', [AttendanceCorrectionController::class, 'store']);

    // 修正申請一覧
    Route::get('/corrections', [AttendanceCorrectionController::class, 'index']);
});


/*
|--------------------------------------------------------------------------
| 管理者ログイン
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    // 管理者ログイン画面
    Route::get('/login', [AdminAuthController::class, 'showLogin']);
    Route::post('/login', [AdminAuthController::class, 'login']);
});


/*
|--------------------------------------------------------------------------
| 管理者（auth + admin 必須）
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        // 管理者トップ（勤怠一覧）
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('admin.index');

        // ユーザー一覧
        Route::get('/users', [AdminUserController::class, 'index']);

        // ユーザー勤怠一覧
        Route::get('/users/{id}/attendance', [AdminAttendanceController::class, 'show']);

        // 修正申請一覧
        Route::get('/corrections', [AdminCorrectionController::class, 'index']);

        // 修正申請承認
        Route::post('/corrections/{id}/approve', [AdminCorrectionController::class, 'approve']);

        // 勤怠詳細（管理者）
        Route::get('/attendances/{id}',             [AdminAttendanceController::class, 'detail'])
        ->name('admin.attendances.detail');

        // 勤怠詳細（修正）
        Route::post('/admin/attendances/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendances.update');

        Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

        Route::get(
        '/staff/list',
        [AdminUserController::class, 'index']
        )->name('admin.staff.list');

        Route::get(
        '/attendance/staff/{id}',
        [AdminUserController::class, 'attendance']
        )->name('admin.staff.attendance');

        Route::get(
        '/staff/{id}/csv',
        [AdminUserController::class, 'csv']
        )->name('admin.staff.csv');    });

Route::get(
    '/stamp_correction_request/list',
    [AdminCorrectionController::class, 'index']
)->name('admin.corrections.list');

Route::get(
    '/stamp_correction_request/{id}',
    [AdminCorrectionController::class, 'show']
)->name('admin.corrections.show');

Route::post(
    '/stamp_correction_request/{id}/approve',
    [AdminCorrectionController::class, 'approve']
)->name('admin.corrections.approve');

/*
|--------------------------------------------------------------------------
| 管理者ログアウト
|--------------------------------------------------------------------------
*/
Route::post('/admin/logout', function () {
    Auth::logout();
    return redirect('/admin/login');
})->middleware('auth');