@echo off
echo بدء تنظيف مشروع Flutter وتحديث التبعيات...

:: تنظيف المشروع
echo جاري تشغيل flutter clean...
flutter clean

:: تحديث التبعيات
echo جاري تشغيل flutter pub get...
flutter pub get

:: التحقق من الأخطاء في الكود
echo جاري تشغيل flutter analyze...
flutter analyze

echo اكتمل العملية. يرجى التحقق من المخرجات لمعرفة أي أخطاء.
pause