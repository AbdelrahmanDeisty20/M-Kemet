<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Otp;
use App\Models\RefreshToken;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * تنظيف رموز الـ OTP المنتهية الصلاحية تلقائياً كل ساعة
 */
// تنفيذ الـ Queue Worker للمهام المعلقة وإنهاؤه فوراً عند الفراغ لمنع استهلاك السيرفر
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();

// تنظيف التوكنات
Schedule::command('auth:clear-resets')->hourly();
Schedule::command('sanctum:prune-expired --hours=24')->daily();
