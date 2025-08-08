@echo off
cls
echo ===================================
echo    NOKTA SAAS - SIMPLE FIX
echo ===================================
echo.

cd /d C:\flutter_projects\nokta_saas

echo Step 1: Cleaning everything...
rd /s /q packages\core\.dart_tool 2>nul
rd /s /q packages\core\build 2>nul
del /q packages\core\pubspec.lock 2>nul

rd /s /q apps\admin_panel\.dart_tool 2>nul
rd /s /q apps\admin_panel\build 2>nul
del /q apps\admin_panel\pubspec.lock 2>nul

rd /s /q apps\customer_app\.dart_tool 2>nul
rd /s /q apps\customer_app\build 2>nul
del /q apps\customer_app\pubspec.lock 2>nul

rd /s /q apps\driver_app\.dart_tool 2>nul
rd /s /q apps\driver_app\build 2>nul
del /q apps\driver_app\pubspec.lock 2>nul

rd /s /q apps\pos_app\.dart_tool 2>nul
rd /s /q apps\pos_app\build 2>nul
del /q apps\pos_app\pubspec.lock 2>nul

echo.
echo Step 2: Installing Melos...
call dart pub global activate melos

echo.
echo Step 3: Melos Bootstrap...
call melos bootstrap

echo.
echo Step 4: Getting packages for core...
cd packages\core
call flutter pub get
cd ..\..

echo.
echo Step 5: Getting packages for apps...
cd apps\admin_panel
call flutter pub get
cd ..\customer_app
call flutter pub get
cd ..\driver_app
call flutter pub get
cd ..\pos_app
call flutter pub get
cd ..\..

echo.
echo Step 6: Applying dart fixes...
call dart fix --apply

echo.
echo ===================================
echo    DONE! Check with:
echo    flutter analyze
echo ===================================
echo.
pause
