<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\PasswordResetController;
use App\Http\Controllers\API\Candidate\CandidateProfileController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\ExperienceLevelController;
use App\Http\Controllers\API\GenderController;
use App\Http\Controllers\API\ProfessionController;
use App\Http\Controllers\API\QualificationController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\ContactRequestController;
use App\Http\Controllers\API\JobSeekerController;
use App\Http\Controllers\API\MigrationController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\TermController;

Route::match(['get', 'post'], '/migrate', [MigrationController::class, 'run']);

Route::middleware([SetLocale::class])->group(function () {
    // المسارات العامة (Public Routes)
    Route::get('/genders', [GenderController::class, 'index']);
    Route::get('/countries', [CountryController::class, 'index']);
    Route::get('/countries/top-6', [CountryController::class, 'top6']);
    Route::get('/countries/popular', [CountryController::class, 'top6']);

    Route::get('/professions', [ProfessionController::class, 'index']);
    Route::get('/professions/top-6', [ProfessionController::class, 'top6']);
    Route::get('/professions/popular', [ProfessionController::class, 'top6']);

    Route::get('/experience-levels', [ExperienceLevelController::class, 'index']);
    Route::get('/qualifications', [QualificationController::class, 'index']);

    Route::get('/terms', [TermController::class, 'index']);
    Route::get('/terms/{id}', [TermController::class, 'show']);

    Route::get('/job-seekers', [JobSeekerController::class, 'index']);
    Route::get('/job-seekers/search', [JobSeekerController::class, 'search']);
    Route::get('/job-seekers/filter', [JobSeekerController::class, 'filter']);
    Route::get('/job-seekers/filter-options', [JobSeekerController::class, 'filterOptions']);
    Route::get('/job-seekers/{id}', [JobSeekerController::class, 'show']);

    Route::post('/register/company', [AuthController::class, 'register']);
    Route::post('/register/candidate', [AuthController::class, 'registerCandidate']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    // نسيان واستعادة كلمة المرور
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetOtp']);
    Route::post('/verify-reset-otp', [PasswordResetController::class, 'verifyResetOtp']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

    // FCM Token Route
    Route::post('/fcm-token', [NotificationController::class, 'sendToken']);

    // المسارات المحمية بـ Sanctum (Protected Routes)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAllDevices']);
        Route::delete('/delete-account', [AuthController::class, 'deleteAccount']);

        // مسارات الإشعارات (Notifications)
        Route::get('/notification/status', [NotificationController::class, 'NotificationStatus']);
        Route::post('/notification/turn-on', [NotificationController::class, 'TurnOnNotification']);
        Route::post('/notification/turn-off', [NotificationController::class, 'TurnOffNotification']);
        Route::get('/notifications', [NotificationController::class, 'notifications']);
        Route::get('/notifications/{id}/read', [NotificationController::class, 'readNotification']);
        Route::get('/notifications/read-all', [NotificationController::class, 'readAllNotifications']);
        Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAllNotifications']);
        Route::delete('/notifications/{id}/delete', [NotificationController::class, 'deleteNotification']);
        Route::post('/fcm-token-user', [NotificationController::class, 'sendToken']);
        Route::post('/sendTestNotification', [NotificationController::class, 'sendTestNotificationToUser']);

        // مسارات حفظ الباحثين عن العمل في المفضلة (Bookmarks)
        Route::get('/bookmarks', [JobSeekerController::class, 'bookmarkedList']);
        Route::post('/job-seekers/{id}/bookmark', [JobSeekerController::class, 'toggleBookmark']);

        // مسارات طلبات التواصل / التوظيف (Contact Requests / Applications)
        Route::post('/job-seekers/{id}/contact-request', [ContactRequestController::class, 'store']);
        Route::get('/company/contact-requests', [ContactRequestController::class, 'companyRequests']);
        Route::get('/candidate/contact-requests', [ContactRequestController::class, 'candidateRequests']);

        // مسارات خاصة بحسابات الشركات (Company / Employer Routes)
        // middleware('company') يمنع الباحثين عن العمل من الوصول لهذه المسارات
        Route::get('/my-requests', [ContactRequestController::class, 'companyRequests'])->middleware('company');
        Route::prefix('company')->middleware('company')->group(function () {
            Route::get('/my-requests', [ContactRequestController::class, 'companyRequests']);
        });

        // مسارات تكميل ملف الباحث عن عمل (Candidate Profile Completion)
        // middleware('candidate') يمنع الشركات من الوصول لهذه المسارات
        Route::prefix('candidate')->middleware('candidate')->group(function () {
            Route::get('/my-document', [CandidateProfileController::class, 'show']);
            Route::put('/update-document', [CandidateProfileController::class, 'update']);
            Route::post('/documents', [CandidateProfileController::class, 'uploadDocument']);
            Route::post('/video', [CandidateProfileController::class, 'uploadVideo']);
            Route::get('/documents/{document}/file', [DocumentController::class, 'viewFile'])
                ->name('documents.file');
        });
    });
});

