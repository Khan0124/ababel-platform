@echo off
cls
cd /d C:\flutter_projects\nokta_saas

echo NOKTA FIX - STEP BY STEP
echo =========================
echo.

echo Step 1: Clean...
rd /s /q packages\core\.dart_tool 2>nul
rd /s /q packages\core\build 2>nul
del packages\core\pubspec.lock 2>nul

echo Step 2: Melos...
dart pub global activate melos

echo Step 3: Bootstrap...
melos bootstrap

echo Step 4: Core...
cd packages\core
flutter pub get
flutter pub run build_runner build --delete-conflicting-outputs
cd ..\..

echo Step 5: Apps...
cd apps\admin_panel & flutter pub get & cd ..\..
cd apps\customer_app & flutter pub get & cd ..\..
cd apps\driver_app & flutter pub get & cd ..\..
cd apps\pos_app & flutter pub get & cd ..\..

echo Step 6: Fix...
dart fix --apply

echo.
echo DONE! Run: flutter analyze
pause
