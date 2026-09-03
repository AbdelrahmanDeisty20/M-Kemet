<?php

namespace App\Filament\Resources\Documents\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات صاحب المستندات والباحث عن العمل')
                    ->icon('heroicon-o-user')
                    ->columns(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('name')
                            ->label('اسم المرشح / المستخدم')
                            ->default(fn ($record) => $record->phone ?? $record->email ?? '-'),
                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->label('رقم الهاتف')
                            ->placeholder('-'),
                        TextEntry::make('candidateProfile.status')
                            ->label('حالة تفعيل الباحث عن العمل')
                            ->badge()
                            ->color(fn ($state): string => match ($state) {
                                'approved' => 'success',
                                'pending'  => 'warning',
                                'rejected' => 'danger',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn ($state): string => match ($state) {
                                'approved' => 'مفعل / مقبول',
                                'pending'  => 'قيد المراجعة',
                                'rejected' => 'مرفوض',
                                default    => $state ?? 'غير محدد',
                            })
                            ->suffixAction(
                                Action::make('changeCandidateStatus')
                                    ->label('تغيير الحالة')
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('primary')
                                    ->form([
                                        Select::make('status')
                                            ->label('الحالة الجديدة لملف الباحث عن العمل')
                                            ->options([
                                                'approved' => 'مفعل / مقبول',
                                                'pending'  => 'قيد المراجعة',
                                                'rejected' => 'مرفوض',
                                            ])
                                            ->default(fn ($record) => $record->candidateProfile?->status ?? 'pending')
                                            ->required()
                                            ->reactive(),
                                        TextInput::make('rejection_reason')
                                            ->label('سبب الرفض')
                                            ->visible(fn ($get) => $get('status') === 'rejected'),
                                    ])
                                    ->action(function ($record, array $data) {
                                        if ($record->candidateProfile) {
                                            $record->candidateProfile->update([
                                                'status'           => $data['status'],
                                                'rejection_reason' => $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? 'تم رفض الملف') : null,
                                            ]);

                                            Notification::make()
                                                ->title('تم تحديث حالة ملف الباحث عن عمل بنجاح')
                                                ->success()
                                                ->send();
                                        }
                                    })
                            ),
                    ]),

                Section::make('الفيديو التعريفي للمرشح (Intro Video) 🎥')
                    ->icon('heroicon-o-video-camera')
                    ->columns(3)
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->video !== null)
                    ->schema([
                        TextEntry::make('video.status')
                            ->label('حالة تفعيل الفيديو')
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
                            })
                            ->suffixAction(
                                Action::make('changeVideoStatus')
                                    ->label('تغيير حالة الفيديو')
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('warning')
                                    ->form([
                                        Select::make('status')
                                            ->label('الحالة الجديدة للفيديو')
                                            ->options([
                                                'approved' => 'معتمد',
                                                'pending'  => 'قيد المراجعة',
                                                'rejected' => 'مرفوض',
                                            ])
                                            ->default(fn ($record) => $record->video?->status ?? 'pending')
                                            ->required()
                                            ->reactive(),
                                        TextInput::make('rejection_reason')
                                            ->label('سبب الرفض')
                                            ->visible(fn ($get) => $get('status') === 'rejected'),
                                    ])
                                    ->action(function ($record, array $data) {
                                        if ($record->video) {
                                            $record->video->update([
                                                'status'           => $data['status'],
                                                'rejection_reason' => $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? 'تم رفض الفيديو') : null,
                                            ]);

                                            Notification::make()
                                                ->title('تم تحديث حالة الفيديو التعريفي بنجاح')
                                                ->success()
                                                ->send();
                                        }
                                    })
                            ),
                        TextEntry::make('video.duration_seconds')
                            ->label('مدة الفيديو')
                            ->suffix(' ثانية')
                            ->placeholder('-'),
                        TextEntry::make('video.file_size_mb')
                            ->label('حجم الملف')
                            ->suffix(' MB')
                            ->placeholder('-'),
                        TextEntry::make('video.rejection_reason')
                            ->label('سبب الرفض المسجل للفيديو')
                            ->placeholder('لا يوجد سبب رفض')
                            ->visible(fn ($record) => $record->video?->status === 'rejected')
                            ->columnSpanFull(),
                        ViewEntry::make('video_player')
                            ->label('معاينة ومشاهدة الفيديو')
                            ->view('filament.resources.documents.video-player')
                            ->columnSpanFull(),
                    ]),

                Section::make('قائمة المستندات المرفوعة التفصيلية')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('documents')
                            ->label('المستندات المرفوعة')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('document_type')
                                    ->label('نوع المستند')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'cv'             => 'success',
                                        'national_id'    => 'info',
                                        'passport'       => 'warning',
                                        'personal_photo' => 'purple',
                                        default          => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'cv'             => 'السيرة الذاتية (CV)',
                                        'national_id'    => 'الهوية الوطنية',
                                        'passport'       => 'جواز السفر',
                                        'personal_photo' => 'صورة شخصية',
                                        default          => $state,
                                    }),

                                TextEntry::make('is_approved')
                                    ->label('حالة الاعتماد بالحاعدة البيانات')
                                    ->badge()
                                    ->state(fn ($record) => $record->is_approved ? 'مقبول / معتمد' : ($record->rejection_reason ? 'مرفوض' : 'قيد المراجعة'))
                                    ->color(fn (string $state) => match (true) {
                                        str_contains($state, 'مقبول')  => 'success',
                                        str_contains($state, 'مرفوض') => 'danger',
                                        default                       => 'warning',
                                    })
                                    ->suffixAction(
                                        Action::make('changeDocumentStatus')
                                            ->label('تغيير حالة المستند')
                                            ->icon('heroicon-o-pencil-square')
                                            ->color('primary')
                                            ->form([
                                                Select::make('status')
                                                    ->label('الحالة الجديدة للمستند')
                                                    ->options([
                                                        'approved' => 'مقبول / معتمد',
                                                        'pending'  => 'قيد المراجعة',
                                                        'rejected' => 'مرفوض',
                                                    ])
                                                    ->default(fn ($record) => $record->is_approved ? 'approved' : ($record->rejection_reason ? 'rejected' : 'pending'))
                                                    ->required()
                                                    ->reactive(),
                                                TextInput::make('rejection_reason')
                                                    ->label('سبب الرفض')
                                                    ->visible(fn ($get) => $get('status') === 'rejected'),
                                            ])
                                            ->action(function ($record, array $data) {
                                                $isApproved = $data['status'] === 'approved';
                                                $rejectionReason = $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? 'تم رفض المستند') : null;

                                                $record->update([
                                                    'is_approved'      => $isApproved,
                                                    'rejection_reason' => $rejectionReason,
                                                ]);

                                                Notification::make()
                                                    ->title('تم تحديث حالة المستند بنجاح')
                                                    ->success()
                                                    ->send();
                                            })
                                    ),

                                ViewEntry::make('file_preview')
                                    ->label('معاينة وتحميل المستند')
                                    ->view('filament.resources.documents.document-file-preview')
                                    ->columnSpanFull(),

                                TextEntry::make('rejection_reason')
                                    ->label('سبب الرفض المسجل')
                                    ->placeholder('لا يوجد سبب رفض')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
