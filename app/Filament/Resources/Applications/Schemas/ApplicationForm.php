<?php

namespace App\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->label('الشركة')
                    ->relationship('company', 'company_name')
                    ->required()
                    ->disabledOn('edit'),
                Select::make('candidate_profile_id')
                    ->label('الباحث عن عمل')
                    ->relationship('candidateProfile', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->name ?? "بروفايل رقم #{$record->id}")
                    ->required()
                    ->disabledOn('edit'),
                Select::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'pending'   => 'قيد الانتظار',
                        'accepted'  => 'مقبول / تم التوافق',
                        'rejected'  => 'مرفوض',
                        'completed' => 'مكتمل',
                    ])
                    ->required(),
                Textarea::make('notes')
                    ->label('الملاحظات')
                    ->columnSpanFull(),
            ]);
    }
}
