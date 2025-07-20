<?php
class Translator {
    private static $locale = 'ar'; // اللغة الافتراضية عربي
    private static $translations = [];

    // تهيئة النظام
    public static function init() {
        if (isset($_SESSION['lang'])) {
            self::$locale = $_SESSION['lang'];
        } else {
            self::$locale = 'ar';
            $_SESSION['lang'] = 'ar';
        }
        self::loadTranslations();
    }

    // تحميل الترجمات
    private static function loadTranslations() {
        $file = __DIR__."/../languages/".self::$locale."/translations.php";
        if(file_exists($file)) {
            self::$translations = include($file);
        }
    }

    // تغيير اللغة
    public static function setLocale($locale) {
        self::$locale = $locale;
        $_SESSION['lang'] = $locale;
        self::loadTranslations();
    }

    // الحصول على الترجمة
    public static function get($key) {
        return self::$translations[$key] ?? $key;
    }
}

// بدء النظام التلقائي
Translator::init();
?>