<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UsersRegistrationChartWidget extends ChartWidget
{
    protected ?string $heading = 'معدل تسجيل المستخدمين الجدد (آخر 6 أشهر)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $months = collect();
        $candidateCounts = collect();
        $companyCounts = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthName = $month->translatedFormat('M Y');
            $months->push($monthName);

            $candidateCount = User::where('user_type', 'candidate')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $companyCount = User::where('user_type', 'company')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $candidateCounts->push($candidateCount);
            $companyCounts->push($companyCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'الباحثين عن عمل',
                    'data'  => $candidateCounts->toArray(),
                    'borderColor' => '#0284c7', // Sky Blue
                    'backgroundColor' => 'rgba(2, 132, 199, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'الشركات ومقدمي الخدمة',
                    'data'  => $companyCounts->toArray(),
                    'borderColor' => '#f59e0b', // Amber
                    'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
