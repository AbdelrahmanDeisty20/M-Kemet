<?php

namespace App\Services;

use App\Filament\Resources\Applications\ApplicationResource;
use App\Filament\Resources\Documents\DocumentResource;
use App\Models\Application;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class AdminNotificationService
{
    /**
     * جلب كافة حسابات الأدمن المسؤولة عن استقبال الإشعارات
     */
    protected static function getAdmins()
    {
        return User::where('user_type', 'admin')->get();
    }

    /**
     * 1. إشعار الأدمن عند تسجيل حساب جديد (باحث عن عمل / شركة)
     */
    public static function notifyNewUserRegistered(User $user): void
    {
        $admins = static::getAdmins();
        if ($admins->isEmpty()) return;

        if ($user->isCompany()) {
            $user->load('company');
            $companyName = $user->company?->company_name ?? $user->name ?? 'شركة جديدة';
            $title = 'تسجيل حساب شركة جديد 🏢';
            $body = "قامت الشركة ({$companyName}) بالانضمام للمنصة.\n📱 رقم الهاتف: {$user->phone}";
            $color = 'info';
            $icon = 'heroicon-o-building-office-2';
        } else {
            $candidateName = $user->name ?? 'مرشح جديد';
            $title = 'تسجيل حساب باحث عن عمل جديد 👤';
            $body = "قام الباحث عن العمل ({$candidateName}) بالانضمام للمنصة.\n📱 رقم الهاتف: {$user->phone}";
            $color = 'success';
            $icon = 'heroicon-o-user-plus';
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon($icon)
            ->color($color)
            ->sendToDatabase($admins);
    }

    /**
     * 2. إشعار الأدمن عند رفع / تحديث المستندات والوثائق (إشعار موحد يوضح المرفوع والمتبقي)
     */
    public static function notifyDocumentUploaded(User $user): void
    {
        $admins = static::getAdmins();
        if ($admins->isEmpty()) return;

        $user->load(['documents', 'video']);

        $docTypesMap = [
            'cv'             => 'السيرة الذاتية (CV)',
            'national_id'    => 'الهوية الوطنية',
            'passport'       => 'جواز السفر',
            'personal_photo' => 'صورة شخصية',
        ];

        $uploadedList = [];
        $existingDocTypes = [];

        foreach ($user->documents as $doc) {
            $existingDocTypes[] = $doc->document_type;
            $uploadedList[] = $docTypesMap[$doc->document_type] ?? $doc->document_type;
        }

        if ($user->video) {
            $existingDocTypes[] = 'video';
            $uploadedList[] = 'الفيديو التعريفي 🎥';
        }

        $allRequired = ['cv', 'national_id', 'passport', 'personal_photo', 'video'];
        $missingList = [];

        foreach ($allRequired as $req) {
            if (!in_array($req, $existingDocTypes)) {
                $missingList[] = match ($req) {
                    'cv'             => 'السيرة الذاتية (CV)',
                    'national_id'    => 'الهوية الوطنية',
                    'passport'       => 'جواز السفر',
                    'personal_photo' => 'صورة شخصية',
                    'video'          => 'الفيديو التعريفي 🎥',
                    default          => $req,
                };
            }
        }

        $uploadedText = !empty($uploadedList) ? implode(' - ', $uploadedList) : 'لا يوجد وثائق مرفوعة';
        $missingText  = !empty($missingList)  ? implode(' - ', $missingList)  : '✅ تم إكمال كافة الوثائق المطلوبة';

        $candidateName = $user->name ?? $user->phone;
        $title = 'تحديث مستندات ووثائق المرشح 📄';
        $body = "👤 المرشح: {$candidateName} | 📱 الهاتف: {$user->phone}\n"
              . "📌 الوثائق المرفوعة: {$uploadedText}\n"
              . "⏳ الوثائق المتبقية / المفقودة: {$missingText}";

        $viewUrl = DocumentResource::getUrl('view', ['record' => $user->id]);

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-document-check')
            ->color('warning')
            ->actions([
                Action::make('view_documents')
                    ->label('معاينة المستندات')
                    ->url($viewUrl),
            ])
            ->sendToDatabase($admins);
    }

    /**
     * 3. إشعار الأدمن عند حذف حساب
     */
    public static function notifyAccountDeleted(User $user): void
    {
        $admins = static::getAdmins();
        if ($admins->isEmpty()) return;

        $typeLabel = $user->isCompany() ? 'شركة' : 'باحث عن عمل';
        $name = $user->isCompany() ? ($user->company?->company_name ?? $user->name) : $user->name;

        $title = 'حذف حساب من المنصة ❌';
        $body = "قام حساب ({$typeLabel}: {$name}) بحذف حسابه نهائياً من المنصة.\n📱 رقم الهاتف: {$user->phone}";

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->sendToDatabase($admins);
    }

    /**
     * 4. إشعار الأدمن عند إرسال طلب تواصل من شركة لمرشح
     */
    public static function notifyContactRequestSent(Application $application): void
    {
        $admins = static::getAdmins();
        if ($admins->isEmpty()) return;

        $application->load(['company.user', 'candidateProfile.user']);

        $company = $application->company;
        $companyUser = $company?->user;
        $companyName = $company?->company_name ?? $companyUser?->name ?? 'غير محدد';
        $companyPhone = $companyUser?->phone ?? 'لا يوجد هاتف';

        $candidateProfile = $application->candidateProfile;
        $candidateUser = $candidateProfile?->user;
        $candidateName = $candidateUser?->name ?? 'غير محدد';
        $candidatePhone = $candidateUser?->phone ?? 'لا يوجد هاتف';

        $notesText = !empty($application->notes) ? $application->notes : 'لا يوجد ملاحظات';

        $title = 'طلب تواصل جديد من شركة لمرشح 💼';
        $body = "🏢 الشركة: {$companyName} (📱 هاتف: {$companyPhone})\n"
              . "👤 الباحث عن العمل: {$candidateName} (📱 هاتف: {$candidatePhone})\n"
              . "📝 الملاحظات: {$notesText}";

        $viewUrl = ApplicationResource::getUrl('view', ['record' => $application->id]);

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-briefcase')
            ->color('primary')
            ->actions([
                Action::make('view_application')
                    ->label('عرض طلب التواصل')
                    ->url($viewUrl),
            ])
            ->sendToDatabase($admins);
    }
}
