<?php

namespace App\Filament\Resources\UserProfiles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات العامة والشخصية')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('اسم الباحث عن عمل')
                            ->placeholder('-'),
                        TextEntry::make('user.email')
                            ->label('البريد الإلكتروني')
                            ->placeholder('-'),
                        TextEntry::make('user.phone')
                            ->label('رقم الهاتف')
                            ->placeholder('-'),
                        TextEntry::make('birth_date')
                            ->label('تاريخ الميلاد')
                            ->date('Y-m-d')
                            ->placeholder('-'),
                        TextEntry::make('genderRelation.name_ar')
                            ->label('الجنس')
                            ->badge()
                            ->color('info')
                            ->placeholder('-'),
                        TextEntry::make('currentCountry.name_ar')
                            ->label('الدولة الحالية')
                            ->placeholder('-'),
                    ]),

                Section::make('البيانات المهنية والمؤهلات')
                    ->icon('heroicon-o-briefcase')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('qualification.name_ar')
                            ->label('المؤهل الدراسي')
                            ->badge()
                            ->color('primary')
                            ->placeholder('-'),
                        TextEntry::make('sub_specialization')
                            ->label('التخصص الفرعي')
                            ->placeholder('-'),
                        TextEntry::make('profession.name_ar')
                            ->label('المهنة الأساسية')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('experience_years')
                            ->label('سنوات الخبرة')
                            ->suffix(' سنة')
                            ->placeholder('-'),
                        TextEntry::make('experienceLevel.name_ar')
                            ->label('مستوى الخبرة')
                            ->placeholder('-'),
                        TextEntry::make('expected_salary')
                            ->label('الراتب المتوقع')
                            ->numeric()
                            ->suffix(' ريال/جنيه')
                            ->placeholder('-'),
                        IconEntry::make('willing_to_travel')
                            ->label('مستعد للسفر بالخارج')
                            ->boolean(),
                        TextEntry::make('targetCountries.name_ar')
                            ->label('الدول المستهدفة')
                            ->badge()
                            ->color('gray')
                            ->placeholder('-'),
                    ]),

                Section::make('الفيديو التعريفي للمرشح (Intro Video)')
                    ->icon('heroicon-o-video-camera')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.video.video_url')
                            ->label('مشاهدة / رابط الفيديو')
                            ->url(fn ($record) => $record?->user?->video?->video_url, shouldOpenInNewTab: true)
                            ->placeholder('لم يتم رفع فيديو تعريفي بعد'),
                        TextEntry::make('user.video.duration_seconds')
                            ->label('مدة الفيديو')
                            ->suffix(' ثانية')
                            ->placeholder('-'),
                        TextEntry::make('user.video.file_size_mb')
                            ->label('حجم الملف')
                            ->suffix(' MB')
                            ->placeholder('-'),
                        TextEntry::make('user.video.status')
                            ->label('حالة اعتماد الفيديو')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'approved' => 'success',
                                'pending'  => 'warning',
                                'rejected' => 'danger',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'approved' => 'معتمد',
                                'pending'  => 'قيد المراجعة',
                                'rejected' => 'مرفوض',
                                default    => 'لا يوجد فيديو',
                            }),
                        TextEntry::make('user.video.rejection_reason')
                            ->label('سبب رفض الفيديو (إن وجد)')
                            ->placeholder('لا يوجد')
                            ->visible(fn ($record) => $record?->user?->video?->status === 'rejected')
                            ->columnSpanFull(),
                    ]),

                Section::make('الملف التعريفي والتقييم')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('summary')
                            ->label('ملخص الباحث عن عمل')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label('حالة الملف')
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

                Section::make('معلومات النظام والتواريخ')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('تاريخ آخر تحديث')
                            ->dateTime('Y-m-d H:i:s'),
                    ]),
            ]);
    }
}
