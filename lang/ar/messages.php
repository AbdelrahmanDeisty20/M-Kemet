<?php

return [
    // General Response Messages
    'operationSuccessful' => 'تمت العملية بنجاح',
    'errorOccurred'       => 'حدث خطأ ما، يرجى المحاولة لاحقاً',
    'notFound'            => 'العنصر المطلوب غير موجود',
    'validationError'     => 'البيانات المدخلة غير صالحة',

    // Authentication Messages
    'accountCreatedSuccessfully'     => 'تم إنشاء الحساب بنجاح، يُرجى إدخال رمز التحقق (OTP) المكون من 6 أرقام المرسل إلى بريدك الإلكتروني',
    'otpVerifiedSuccessfully'        => 'تم تفعيل الحساب وتسجيل الدخول بنجاح',
    'otp_invalid'                    => 'رمز التحقق غير صحيح أو غير موجود',
    'otp_expired'                    => 'انتهت صلاحية رمز التحقق، يرجى طلب رمز جديد',
    'invalidPassword'                => 'كلمة المرور غير صحيحة',
    'invalidCredentials'             => 'البريد الإلكتروني أو كلمة المرور غير صحيحة',
    'userSuspended'                  => 'هذا الحساب معطل، يرجى التواصل مع الدعم الفني',
    'userPendingVerification'        => 'الحساب غير مفعل، يرجى تفعيل الحساب عبر رمز OTP أولاً',
    'otpResentSuccessfully'          => 'تم إعادة إرسال رمز التحقق بنجاح',
    'otp_wait_resend'                => 'يرجى الانتظار دقيقة واحدة قبل طلب رمز جديد',
    'userAlreadyActive'              => 'هذا البريد الإلكتروني مفعل بالفعل',
    'profileSuccessFully'            => 'تم جلب بيانات البروفايل بنجاح',
    'logoutSuccessFully'             => 'تم تسجيل الخروج بنجاح',
    'logoutAllDevicesSuccessFully'   => 'تم تسجيل الخروج من كافة الأجهزة بنجاح',
    'user_not_found'                 => 'المستخدم غير موجود بالمنظومة',
    'resetOtpSentSuccessfully'       => 'تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني',
    'resetOtpValid'                  => 'رمز التحقق صحيح، يمكنك الآن إدخال كلمة المرور الجديدة',
    'passwordResetSuccessfully'      => 'تم تغيير كلمة المرور بنجاح، يمكنك الآن تسجيل الدخول',
    'tokenRefreshedSuccessfully'     => 'تم تجديد التوكن بنجاح',
    'invalid_token'                  => 'الـ Refresh Token غير صالح أو منتهي الصلاحية',

    // Mail Strings
    'otp_mail_subject' => 'رمز التحقق الخاص بك - منصة أم كميت (M-Kemet)',
    'otp_mail_welcome' => 'مرحباً :name،',
    'otp_mail_intro'   => 'شكراً لتسجيلك في منصة أم كميت (M-Kemet). رمز التحقق (OTP) الخاص بك هو:',
    'otp_mail_expiry'  => 'هذا الرمز صالح لمدة 5 دقائق فقط.',
    'otp_mail_ignore'  => 'إذا لم تكن قد طلبت هذا الرمز، يُرجى تجاهل هذه الرسالة.',
];
