<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BiometricController;
use App\Http\Controllers\JobPostingController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', fn() => redirect()->route('login'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login',  [AuthenticatedSessionController::class,'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class,'store']);
    Route::get('register',  [RegisteredUserController::class,'create'])->name('register');
    Route::post('register', [RegisteredUserController::class,'store']);
    Route::get('forgot-password',   [PasswordResetLinkController::class,'create'])->name('password.request');
    Route::post('forgot-password',  [PasswordResetLinkController::class,'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class,'create'])->name('password.reset');
    Route::post('reset-password',        [NewPasswordController::class,'store'])->name('password.store');
});

Route::post('logout', [AuthenticatedSessionController::class,'destroy'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth','checkRole'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class,'index'])->name('dashboard');

    // Shared Employee Access
    Route::post('employees/{employee}/password', [EmployeeController::class,'updatePassword'])->name('employees.update-password');

    // ── Employees ─────────────────────────────────────────────────────────
    Route::middleware('checkRole:admin,hr_manager')->group(function () {
        Route::get('employees',                  [EmployeeController::class,'index'])->name('employees.index');
        Route::get('employees/create',           [EmployeeController::class,'create'])->name('employees.create');
        Route::post('employees',                 [EmployeeController::class,'store'])->name('employees.store');
        Route::get('employees/{employee}/edit',  [EmployeeController::class,'edit'])->name('employees.edit');
        Route::put('employees/{employee}',       [EmployeeController::class,'update'])->name('employees.update');
        Route::post('employees/{employee}/archive', [EmployeeController::class,'archive'])->name('employees.archive');
        Route::post('employees/{employee}/restore', [EmployeeController::class,'restore'])->name('employees.restore');
        Route::delete('employees/{employee}',    [EmployeeController::class,'destroy'])->name('employees.destroy');
    });

    Route::get('employees/{employee}', [EmployeeController::class,'show'])->name('employees.show');

    // ── Departments ────────────────────────────────────────────────────────
    Route::middleware('checkRole:admin,hr_manager')->group(function () {
        Route::get('departments',                    [DepartmentController::class,'index'])->name('departments.index');
        Route::get('departments/create',             [DepartmentController::class,'create'])->name('departments.create');
        Route::post('departments',                   [DepartmentController::class,'store'])->name('departments.store');
        Route::get('departments/{department}',       [DepartmentController::class,'show'])->name('departments.show');
        Route::get('departments/{department}/edit',  [DepartmentController::class,'edit'])->name('departments.edit');
        Route::put('departments/{department}',       [DepartmentController::class,'update'])->name('departments.update');
        Route::post('departments/{department}/archive', [DepartmentController::class,'archive'])->name('departments.archive');
        Route::delete('departments/{department}',    [DepartmentController::class,'destroy'])->name('departments.destroy');
    });

    // ── Attendance ─────────────────────────────────────────────────────────
    Route::get('attendance',        [AttendanceController::class,'index'])->name('attendance.index');
    Route::get('attendance/my',     [AttendanceController::class,'myAttendance'])->name('attendance.my');
    Route::post('attendance/clock', [AttendanceController::class,'clockIn'])->name('attendance.clock');

    Route::middleware('checkRole:admin,hr_manager')->group(function () {
        Route::get('attendance/create',               [AttendanceController::class,'create'])->name('attendance.create');
        Route::post('attendance',                     [AttendanceController::class,'store'])->name('attendance.store');
        Route::get('attendance/{attendance}/edit',    [AttendanceController::class,'edit'])->name('attendance.edit');
        Route::put('attendance/{attendance}',         [AttendanceController::class,'update'])->name('attendance.update');
        Route::delete('attendance/{attendance}',      [AttendanceController::class,'destroy'])->name('attendance.destroy');
    });

    // ── Biometrics ─────────────────────────────────────────────────────────
    Route::middleware('checkRole:admin,hr_manager')->group(function () {
        Route::get('biometric',          [BiometricController::class,'index'])->name('biometric.index');
        Route::post('biometric/process', [BiometricController::class,'processLogs'])->name('biometric.process');
        Route::post('biometric/tap',     [BiometricController::class,'tap'])->name('biometric.tap');
    });

    // ── Leaves ─────────────────────────────────────────────────────────────
    Route::get('leaves',              [LeaveController::class,'index'])->name('leaves.index');
    Route::get('leaves/create',       [LeaveController::class,'create'])->name('leaves.create');
    Route::post('leaves',             [LeaveController::class,'store'])->name('leaves.store');
    Route::get('leaves/{leave}',      [LeaveController::class,'show'])->name('leaves.show');
    Route::get('leaves/{leave}/edit', [LeaveController::class,'edit'])->name('leaves.edit');
    Route::put('leaves/{leave}',      [LeaveController::class,'update'])->name('leaves.update');
    Route::delete('leaves/{leave}',   [LeaveController::class,'destroy'])->name('leaves.destroy');

    Route::middleware('checkRole:admin,hr_manager')->group(function () {
        Route::post('leaves/{leave}/approve', [LeaveController::class,'approve'])->name('leaves.approve');
        Route::post('leaves/{leave}/reject',  [LeaveController::class,'reject'])->name('leaves.reject');
    });
    Route::post('leaves/{leave}/cancel', [LeaveController::class,'cancel'])->name('leaves.cancel');

    // ── Payroll ────────────────────────────────────────────────────────────
    Route::get('payroll/my', [PayrollController::class,'myPayroll'])->name('payroll.my');
    Route::middleware('checkRole:admin,hr_manager,payroll_officer')->group(function () {
        Route::get('payroll',                   [PayrollController::class,'index'])->name('payroll.index');
        Route::get('payroll/create',            [PayrollController::class,'create'])->name('payroll.create');
        Route::post('payroll',                  [PayrollController::class,'store'])->name('payroll.store');
        Route::get('payroll/{payroll}/edit',    [PayrollController::class,'edit'])->name('payroll.edit');
        Route::put('payroll/{payroll}',         [PayrollController::class,'update'])->name('payroll.update');
        Route::delete('payroll/{payroll}',      [PayrollController::class,'destroy'])->name('payroll.destroy');
        Route::post('payroll/generate-bulk',    [PayrollController::class,'generateBulk'])->name('payroll.generate-bulk');
        Route::get('payroll/check-bulk',        [PayrollController::class,'checkBulk'])->name('payroll.check-bulk');
    });

    Route::get('payroll/{payroll}', [PayrollController::class,'show'])->name('payroll.show');

    // ── Recruitment ────────────────────────────────────────────────────────
    Route::middleware('checkRole:admin,hr_manager,job_recruiter')->group(function () {
        Route::get('recruitment',                            [JobPostingController::class,'index'])->name('recruitment.index');
        Route::get('recruitment/create',                     [JobPostingController::class,'create'])->name('recruitment.create');
        Route::post('recruitment',                           [JobPostingController::class,'store'])->name('recruitment.store');
        Route::get('recruitment/{jobPosting}',               [JobPostingController::class,'show'])->name('recruitment.show');
        Route::get('recruitment/{jobPosting}/edit',          [JobPostingController::class,'edit'])->name('recruitment.edit');
        Route::put('recruitment/{jobPosting}',               [JobPostingController::class,'update'])->name('recruitment.update');
        Route::post('recruitment/{jobPosting}/archive',      [JobPostingController::class,'archive'])->name('recruitment.archive');
        Route::get('recruitment/{jobPosting}/applications',  [JobPostingController::class,'applications'])->name('recruitment.applications');
        Route::post('applications/{application}/status',     [JobPostingController::class,'updateApplicationStatus'])->name('applications.status');
    });

    // ── Users ──────────────────────────────────────────────────────────────
    Route::middleware('checkRole:admin')->group(function () {
        Route::get('users',                     [UserController::class,'index'])->name('users.index');
        Route::get('users/create',              [UserController::class,'create'])->name('users.create');
        Route::post('users',                    [UserController::class,'store'])->name('users.store');
        Route::get('users/{user}/edit',         [UserController::class,'edit'])->name('users.edit');
        Route::put('users/{user}',              [UserController::class,'update'])->name('users.update');
        Route::post('users/{user}/archive',     [UserController::class,'archive'])->name('users.archive');
        Route::post('users/{user}/restore',     [UserController::class,'restore'])->name('users.restore');
        Route::post('users/{user}/reset-password', [UserController::class,'resetPassword'])->name('users.reset-password');
    });
});
