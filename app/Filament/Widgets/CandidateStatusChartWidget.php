<?php

namespace App\Filament\Widgets;

use App\Models\UserProfile;
use Filament\Widgets\ChartWidget;

class CandidateStatusChartWidget extends ChartWidget
{
    protected ?string $heading = 'توزيع حالات ملفات الباحثين عن عمل';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $approved = UserProfile::where('status', 'approved')->count();
        $pending  = UserProfile::where('status', 'pending')->count();
        $rejected = UserProfile::where('status', 'rejected')->count();

        return [
            'datasets' => [
                [
                    'label' => 'الحالة',
                    'data'  => [$approved, $pending, $rejected],
                    'backgroundColor' => [
                        '#10b981', // Emerald/Approved
                        '#f59e0b', // Amber/Pending
                        '#ef4444', // Red/Rejected
                    ],
                ],
            ],
            'labels' => [
                'معتمد (' . $approved . ')',
                'قيد المراجعة (' . $pending . ')',
                'مرفوض (' . $rejected . ')',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
