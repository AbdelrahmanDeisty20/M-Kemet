<?php

namespace App\Filament\Resources\Videos\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
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

                Section::make('مشاهدة وتشغيل الفيديو التعريفي 🎥')
                    ->icon('heroicon-o-video-camera')
                    ->columnSpanFull()
                    ->schema([
                        ViewEntry::make('video_player')
                            ->label('تشغيل الفيديو')
                            ->view('filament.resources.documents.video-player')
                            ->columnSpanFull(),
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
                                            ->default(fn ($record) => $record->status ?? 'pending')
                                            ->required()
                                            ->reactive(),
                                        TextInput::make('rejection_reason')
                                            ->label('سبب الرفض')
                                            ->visible(fn ($get) => $get('status') === 'rejected'),
                                    ])
                                    ->action(function ($record, array $data) {
                                        $record->update([
                                            'status'           => $data['status'],
                                            'rejection_reason' => $data['status'] === 'rejected' ? ($data['rejection_reason'] ?? 'تم رفض الفيديو') : null,
                                        ]);

                                        Notification::make()
                                            ->title('تم تحديث حالة الفيديو التعريفي بنجاح')
                                            ->success()
                                            ->send();
                                    })
                            ),
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
