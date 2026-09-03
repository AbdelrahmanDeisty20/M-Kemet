<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VideoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المرشح صاحب الفيديو')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('الاسم الكامل')
                            ->placeholder('-'),
                        TextEntry::make('user.email')
                            ->label('البريد الإلكتروني')
                            ->placeholder('-'),
                        TextEntry::make('user.phone')
                            ->label('رقم الهاتف')
                            ->placeholder('-'),
                    ]),

                Section::make('تفاصيل ومواصفات الفيديو')
                    ->icon('heroicon-o-video-camera')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('video_path')
                            ->label('مشاهدة الفيديو')
                            ->url(fn ($record) => $record?->video_url, shouldOpenInNewTab: true)
                            ->formatStateUsing(fn () => 'فتح ورابط مشاهدة الفيديو 🎥')
                            ->color('primary'),
                        TextEntry::make('duration_seconds')
                            ->label('مدة الفيديو')
                            ->suffix(' ثانية')
                            ->placeholder('-'),
                        TextEntry::make('file_size_mb')
                            ->label('حجم الفيديو')
                            ->suffix(' MB')
                            ->placeholder('-'),
                    ]),

                Section::make('حالة واعتماد الفيديو')
                    ->icon('heroicon-o-check-badge')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('status')
                            ->label('حالة الفيديو')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'approved' => 'success',
                                'pending'  => 'warning',
                                'rejected' => 'danger',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'approved' => 'معتمد',
                                'pending'  => 'قيد المراجعة',
                                'rejected' => 'مرفوض',
                                default    => $state,
                            }),
                        TextEntry::make('rejection_reason')
                            ->label('سبب الرفض (إن وجد)')
                            ->placeholder('لا يوجد')
                            ->visible(fn ($record) => $record?->status === 'rejected')
                            ->columnSpanFull(),
                    ]),

                Section::make('تاريخ الرفع والتسجيل')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الرفع')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i:s'),
                    ]),
            ]);
    }
}
