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
    'LBL_TAB_GENERAL' => 'Genel ayarlar',
    'LBL_STATUS' => 'Durum',
    'LBL_STATUS_INTRO' => 'İki faktörlü kimlik doğrulamayı tüm CRM için açın veya kapatın. Etkinleştirme ikinci giriş adımını uygular; devre dışı bırakma standart girişi geri yükler.',
    'LBL_ACTIVE' => 'Etkin',
    'LBL_INACTIVE' => 'Devre dışı',
    'LBL_ACTIVATE_CONFIRM' => 'Etkinleştirme, tüm kullanıcılar (muaf tutulanlar hariç) için ikinci bir giriş adımı gerektirir. Devam etmeden önce giden e-postanın çalıştığından ve yedek kodları kaydettiğinizden emin olun - aksi takdirde kilitli kalabilirsiniz. Devam edilsin mi?',
    'LBL_DEACTIVATE_CONFIRM' => 'Devre dışı bırakma, ikinci giriş adımını herkes için kaldırır ve orijinal girişi geri yükler. Devam edilsin mi?',
    'LBL_ACTIVATE_FAILED' => 'Giriş değişikliği uygulanamadı. Giriş dosyasının yazılabilir olduğunu kontrol edin.',
    'LBL_NOT_ACTIVE_NOTE' => 'İki faktörlü kimlik doğrulama kuruldu ancak henüz etkin değil. Aşağıdan etkinleştirene kadar ikinci bir giriş adımı gerekmez.',
    'LBL_ENFORCE_ALL_NOTE' => 'İki faktörlü kimlik doğrulama etkin ve tüm kullanıcılar için zorunlu. Varsayılan olarak her kullanıcı girişte e-posta ile tek kullanımlık bir kod alır. Bir kullanıcıyı hariç tutmak için muafiyet listesine ekleyin.',
    'LBL_ENFORCE_OFF_NOTE' => 'İki faktörlü kimlik doğrulama şu anda tüm kullanıcılar için kapalı.',
    'LBL_AVAILABLE_METHODS' => 'Kullanılabilir yöntemler',
    'LBL_AVAILABLE_METHODS_INTRO' => 'Kullanıcıların hangi ikinci faktör yöntemlerini kullanabileceğini seçin. Her kullanıcının girişte seçmesi için ikisini de etkin bırakın; herkese zorunlu kılmak için yalnızca birini etkinleştirin. En az bir yöntem etkin kalmalıdır.',
    'LBL_METHOD_EMAIL' => 'E-posta ile tek kullanımlık kod',
    'LBL_METHOD_TOTP' => 'Kimlik doğrulama uygulaması (TOTP)',
    'LBL_EMAIL_NEEDS_SERVER' => 'E-posta kodlarını etkinleştirmek için bir giden posta sunucusu yapılandırın (Ayarlar > Giden Sunucu).',
    'LBL_TAB_USERS' => 'Kullanıcılar',
    'LBL_USERS_INTRO' => 'Her etkin kullanıcının iki faktörlü kimlik doğrulaması varsayılan olarak açıktır. Bir kullanıcıyı muaf tutmak için kapatın (yalnızca parola), yöntemini değiştirin veya cihazını kaybettiyse kimlik doğrulayıcısını sıfırlayın.',
    'LBL_USER' => 'Kullanıcı',
    'LBL_USER_NOT_FOUND' => 'Kullanıcı bulunamadı.',
    'LBL_2FA' => '2FA',
    'LBL_ACTIONS' => 'İşlemler',
    'LBL_RESET' => 'Sıfırla',
    'LBL_RESET_CONFIRM' => 'Bu kullanıcı için iki faktörlü kimlik doğrulama sıfırlansın mı? Kimlik doğrulama uygulaması ve yedek kodları çalışmayı durduracak ve bir sonraki girişte yeniden kuracaktır.',
    'LBL_METHOD' => 'Yöntem',
    'LBL_UNUSED_CODES' => 'Kullanılmayan yedek kodlar',
    'LBL_EMAILS_INTRO' => 'Bu e-postanın konusunu ve içeriğini özelleştirin. Aşağıdaki değişkenleri kullanın - e-posta gönderildiğinde gerçek değerlerle değiştirilir. Orijinal metni geri yüklemek için Varsayılana sıfırla düğmesini kullanın.',
    'LBL_TPL_TITLE_LOGIN' => 'Giriş doğrulama kodu şablonu',
    'LBL_TPL_TITLE_BACKUP' => 'Yedek kodlar şablonu',
    'LBL_TPL_SUBJECT' => 'Konu',
    'LBL_TPL_BODY' => 'İçerik',
    'LBL_TPL_VARS' => 'Kullanılabilir değişkenler',
    'LBL_RESET_DEFAULT' => 'Varsayılana sıfırla',
];

$jsLanguageStrings = [
    'JS_SAVED' => 'Kaydedildi.',
    'JS_METHOD_SAVED' => 'Yöntem kaydedildi.',
    'JS_METHODS_SAVED' => 'Kullanılabilir yöntemler güncellendi.',
    'JS_AT_LEAST_ONE_METHOD' => 'En az bir yöntem etkin kalmalıdır.',
    'JS_RESET_TPL_CONFIRM' => 'Bu şablon varsayılan metnine sıfırlansın mı?',
];
