<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

$languageStrings = [
    'TwoFactorAuthentication' => '2FA',
    'LBL_2FA_TITLE' => 'التحقق بخطوتين',
    'LBL_EMAIL_PROMPT' => 'أرسلنا رمز تحقق إلى بريدك الإلكتروني. أدخله أدناه للمتابعة.',
    'LBL_TOTP_PROMPT' => 'أدخل الرمز من تطبيق المصادقة للمتابعة.',
    'LBL_CODE_PLACEHOLDER' => 'رمز التحقق',
    'LBL_VERIFY' => 'تحقق',
    'LBL_RESEND_CODE' => 'إعادة إرسال الرمز',
    'LBL_BACK_TO_LOGIN' => 'العودة إلى تسجيل الدخول',
    'LBL_BACKUP_HINT' => 'فقدت الوصول؟ يمكنك إدخال أحد رموز النسخ الاحتياطي بدلاً من ذلك.',
    'LBL_USE_OTHER_METHOD' => 'استخدام طريقة أخرى',
    'LBL_CHOOSE_METHOD' => 'اختر كيف تريد التحقق من هويتك.',
    'LBL_CHOICE_EMAIL' => 'إرسال رمز إلى بريدي الإلكتروني',
    'LBL_CHOICE_EMAIL_SUB' => 'يتم إرسال رمز لمرة واحدة إلى بريدك الإلكتروني',
    'LBL_CHOICE_TOTP' => 'استخدام تطبيق المصادقة الخاص بي',
    'LBL_CHOICE_TOTP_SUB' => 'Google Authenticator، Microsoft Authenticator، Authy...',
    'LBL_ENROLL_PROMPT' => 'امسح رمز QR هذا بتطبيق المصادقة، ثم أدخل الرمز المكوّن من 6 أرقام الذي يظهر لإكمال الإعداد.',
    'LBL_MANUAL_KEY' => 'لا يمكنك المسح؟ أدخل هذا المفتاح يدوياً:',
    'LBL_ENROLL_VERIFY' => 'تأكيد ومتابعة',
    'LBL_BACKUP_CODES_WARNING' => 'احفظ رموز النسخ الاحتياطي هذه في مكان آمن. يعمل كل رمز مرة واحدة ويتيح لك تسجيل الدخول إذا فقدت الوصول إلى تطبيقك. تُعرض الآن فقط.',
    'LBL_BACKUP_CODES' => 'رموز النسخ الاحتياطي',
    'LBL_MANAGE_TITLE' => 'المصادقة الثنائية',
    'LBL_AUTH_METHOD' => 'طريقة المصادقة',
    'LBL_OPT_EMAIL' => 'رمز لمرة واحدة عبر البريد الإلكتروني',
    'LBL_OPT_APP' => 'تطبيق المصادقة',
    'LBL_METHOD_NOT_SET' => '— (يُختار عند تسجيل الدخول)',
    'LBL_ADMIN' => 'مسؤول',
    'LBL_METHOD_SAVED' => 'تم حفظ طريقة المصادقة الخاصة بك.',
    'LBL_APP_STATUS_ON' => 'تم إعداد تطبيق المصادقة الخاص بك.',
    'LBL_APP_STATUS_OFF' => 'لم تقم بإعداد تطبيق مصادقة بعد.',
    'LBL_SETUP_APP' => 'إعداد تطبيق المصادقة',
    'LBL_RECONFIGURE_APP' => 'إعادة تكوين تطبيق المصادقة',
    'LBL_REGEN_BACKUP' => 'إنشاء رموز نسخ احتياطي جديدة',
    'LBL_UNUSED_CODES' => 'رموز النسخ الاحتياطي غير المستخدمة',
    'LBL_BACKUP_CODES_INTRO_USER' => 'تتيح لك رموز النسخ الاحتياطي تسجيل الدخول إذا فقدت الوصول إلى عاملك الثاني. يعمل كل رمز مرة واحدة.',
    'LBL_INVALID_CODE' => 'الرمز الذي أدخلته غير صحيح أو انتهت صلاحيته. يرجى المحاولة مرة أخرى.',
    'LBL_TOO_MANY_ATTEMPTS' => 'محاولات فاشلة كثيرة جداً. يرجى الانتظار %d دقيقة قبل المحاولة مرة أخرى.',
    'LBL_CODE_RESENT' => 'تم إرسال رمز تحقق جديد إلى بريدك الإلكتروني.',
    'LBL_TPL_LOGIN_SUBJECT' => 'رمز التحقق الخاص بك',
    'LBL_TPL_LOGIN_BODY' => '<p>مرحباً $username$،</p><p>رمز التحقق لمرة واحدة الخاص بك هو: <strong>$code$</strong></p><p>صالح لمدة $minutes$ دقيقة. إذا لم تحاول تسجيل الدخول، يرجى الاتصال بالمسؤول.</p><p>$company$</p>',
    'LBL_TPL_BACKUP_SUBJECT' => 'رموز النسخ الاحتياطي للمصادقة الثنائية',
    'LBL_TPL_BACKUP_BODY' => '<p>مرحباً $username$،</p><p>إليك رموز النسخ الاحتياطي الخاصة بك. يعمل كل رمز مرة واحدة:</p><p>$codes$</p><p>احفظها في مكان آمن.</p><p>$company$</p>',
    'LBL_DONE' => 'تم',
];
