<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('البيانات الأساسية للمستخدم')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('الاسم الكامل')
                            ->placeholder('-'),
                        TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->label('رقم الهاتف')
                            ->placeholder('-'),
                    ]),

                Section::make('نوع الحساب وحالة الوصول')
                    ->icon('heroicon-o-shield-check')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user_type')
                            ->label('نوع المستخدم')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'admin'     => 'danger',
                                'company'   => 'warning',
                                'candidate' => 'success',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'admin'     => 'مدير النظام',
                                'company'   => 'شركة / مقدم خدمة',
                                'candidate' => 'باحث عن عمل',
                                default     => $state,
                            }),
                        TextEntry::make('status')
                            ->label('حالة الحساب')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active'    => 'success',
                                'pending'   => 'warning',
                                'suspended' => 'danger',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active'    => 'نشط',
                                'pending'   => 'قيد التفعيل',
                                'suspended' => 'موقوف',
                                default     => $state,
                            }),
                        TextEntry::make('roles.name')
                            ->label('الأدوار والصلاحيات (Roles)')
                            ->badge()
                            ->color('info')
                            ->placeholder('لا توجد أدوار خاصة'),
                        TextEntry::make('country.name_ar')
                            ->label('الدولة')
                            ->placeholder('-'),
                        TextEntry::make('email_verified_at')
                            ->label('تاريخ تفعيل البريد')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('غير مفعل'),
                    ]),

                Section::make('التواريخ والسجلات')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->dateTime('Y-m-d H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('آخر تحديث')
                            ->dateTime('Y-m-d H:i:s'),
                    ]),
            ]);
    }
}
