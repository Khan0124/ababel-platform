@echo off
cls
echo ============================================
echo    FINAL WORKING SOLUTION - 229 ERRORS
echo ============================================
echo.

cd /d C:\flutter_projects\nokta_saas

:: Clean everything first
echo Cleaning all build files...
for /d %%a in (packages\core packages\api_client packages\ui_kit apps\admin_panel apps\customer_app apps\driver_app apps\pos_app) do (
    if exist "%%a\.dart_tool" rd /s /q "%%a\.dart_tool" 2>nul
    if exist "%%a\build" rd /s /q "%%a\build" 2>nul
    if exist "%%a\pubspec.lock" del /q "%%a\pubspec.lock" 2>nul
    if exist "%%a\.flutter-plugins" del /q "%%a\.flutter-plugins" 2>nul
    if exist "%%a\.flutter-plugins-dependencies" del /q "%%a\.flutter-plugins-dependencies" 2>nul
    if exist "%%a\.packages" del /q "%%a\.packages" 2>nul
)

:: Clean flutter cache
echo Cleaning Flutter cache...
call flutter clean

:: Install Melos
echo.
echo Installing Melos...
call dart pub global activate melos

:: Bootstrap with Melos
echo.
echo Running Melos bootstrap...
call melos clean
call melos bootstrap

:: Get dependencies for core first
echo.
echo Getting core dependencies...
cd packages\core
call flutter clean
call flutter pub cache repair
call flutter pub get
call flutter pub upgrade

:: Generate code for core
echo.
echo Generating code...
call flutter pub run build_runner clean
call flutter pub run build_runner build --delete-conflicting-outputs

:: Back to root
cd ..\..

:: Fix each app one by one
echo.
echo Fixing applications...

:: Admin Panel
if exist "apps\admin_panel" (
    echo Fixing admin_panel...
    cd apps\admin_panel
    call flutter clean
    call flutter pub get
    call flutter pub upgrade
    cd ..\..
)

:: Customer App
if exist "apps\customer_app" (
    echo Fixing customer_app...
    cd apps\customer_app
    call flutter clean
    call flutter pub get
    call flutter pub upgrade
    cd ..\..
)

:: Driver App
if exist "apps\driver_app" (
    echo Fixing driver_app...
    cd apps\driver_app
    call flutter clean
    call flutter pub get
    call flutter pub upgrade
    cd ..\..
)

:: POS App
if exist "apps\pos_app" (
    echo Fixing pos_app...
    cd apps\pos_app
    call flutter clean
    call flutter pub get
    call flutter pub upgrade
    cd ..\..
)

:: Apply dart fixes
echo.
echo Applying automatic fixes...
call dart fix --apply

:: Try Melos analyze if available
echo.
echo Running analysis...
call melos analyze

:: Final check
echo.
echo ============================================
echo Checking for errors...
echo ============================================
call flutter analyze

echo.
echo ============================================
echo DONE! If errors remain:
echo 1. Run: dart fix --apply
echo 2. Run: flutter analyze
echo ============================================
pause
