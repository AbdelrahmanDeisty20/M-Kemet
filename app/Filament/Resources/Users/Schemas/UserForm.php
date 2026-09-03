<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('الاسم الكامل')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('رقم الهاتف')
                    ->tel()
                    ->maxLength(20),
                Select::make('user_type')
                    ->label('نوع المستخدم')
                    ->options([
                        'candidate' => 'باحث عن عمل',
                        'company'   => 'شركة',
                        'admin'     => 'مدير',
                    ])
                    ->default('candidate')
                    ->required(),
                Select::make('status')
                    ->label('حالة الحساب')
                    ->options([
                        'active'    => 'نشط',
                        'pending'   => 'قيد التفعيل',
                        'suspended' => 'موقوف',
                    ])
                    ->default('active')
                    ->required(),
                Select::make('roles')
                    ->label('الأدوار (Roles)')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                DateTimePicker::make('email_verified_at')
                    ->label('تاريخ التحقق من البريد'),
                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
            ]);
    }
}
