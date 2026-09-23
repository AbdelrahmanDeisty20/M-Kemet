<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Models\Company;
use App\Models\Document;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Video;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalCandidates = User::where('user_type', 'candidate')->count();
        $pendingCandidates = UserProfile::where('status', 'pending')->count();

        $totalCompanies = Company::count();
        $pendingCompanies = Company::where('status', 'pending')->count();

        $totalVideos = Video::count();
        $pendingVideos = Video::where('status', 'pending')->count();
        $totalDocuments = Document::count();

        $totalApplications = Application::count();

        return [
            Stat::make('الباحثين عن عمل', number_format($totalCandidates))
                ->description($pendingCandidates > 0 ? "⚠️ {$pendingCandidates} ملف بانتظار الاعتماد" : 'جميع الملفات معتمدة')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($pendingCandidates > 0 ? 'warning' : 'success')
                ->chart([5, 8, 12, 18, 25, $totalCandidates]),

            Stat::make('مقدمي الخدمة (الشركات)', number_format($totalCompanies))
                ->description($pendingCompanies > 0 ? "⏳ {$pendingCompanies} شركة قيد المراجعة" : 'حسابات الشركات ناشطة')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color($pendingCompanies > 0 ? 'amber' : 'info')
                ->chart([2, 4, 6, 10, 15, $totalCompanies]),

            Stat::make('المستندات والفيديوهات', number_format($totalDocuments + $totalVideos))
                ->description($pendingVideos > 0 ? "🎬 {$pendingVideos} فيديو بحاجة للمراجعة" : "إجمالي {$totalVideos} فيديو + {$totalDocuments} مستند")
                ->descriptionIcon('heroicon-m-video-camera')
                ->color($pendingVideos > 0 ? 'danger' : 'primary')
                ->chart([3, 7, 14, 20, 28, $totalDocuments + $totalVideos]),

            Stat::make('طلبات التواصل والتوظيف', number_format($totalApplications))
                ->description('إجمالي طلبات التوظيف بالمنصة')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('emerald')
                ->chart([1, 5, 9, 15, 22, $totalApplications]),
        ];
    }
}
