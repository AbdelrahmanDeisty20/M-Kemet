<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserProfiles\UserProfileResource;
use App\Models\UserProfile;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestCandidatesWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'أحدث طلبات انضمام الباحثين عن عمل';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                UserProfile::query()->latest('created_at')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('الاسم الكامل')
                    ->searchable()
                    ->default('-')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('رقم الهاتف')
                    ->default('-'),

                Tables\Columns\TextColumn::make('profession.name_ar')
                    ->label('المهنة / التخصص')
                    ->default('-')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->label('حالة الاعتماد')
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->label('عرض التفاصيل')
                    ->icon('heroicon-m-eye')
                    ->url(fn (UserProfile $record): string => UserProfileResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
