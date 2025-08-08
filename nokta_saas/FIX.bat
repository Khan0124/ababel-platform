@echo off
cls
color 0A
echo ========================================================
echo     NOKTA SAAS - WORKING FIX FOR 229 ERRORS
echo ========================================================
echo.

cd /d C:\flutter_projects\nokta_saas
if %errorlevel% neq 0 (
    echo ERROR: Cannot find project folder!
    echo Make sure you have C:\flutter_projects\nokta_saas
    pause
    exit
)

echo [1/8] Cleaning old files...
echo ----------------------------------------
for /d %%i in (packages\core packages\api_client packages\ui_kit) do (
    if exist "%%i" (
        if exist "%%i\.dart_tool" rd /s /q "%%i\.dart_tool" 2>nul
        if exist "%%i\build" rd /s /q "%%i\build" 2>nul
        if exist "%%i\pubspec.lock" del /q "%%i\pubspec.lock" 2>nul
    )
)

for /d %%i in (apps\admin_panel apps\customer_app apps\driver_app apps\pos_app) do (
    if exist "%%i" (
        if exist "%%i\.dart_tool" rd /s /q "%%i\.dart_tool" 2>nul
        if exist "%%i\build" rd /s /q "%%i\build" 2>nul
        if exist "%%i\pubspec.lock" del /q "%%i\pubspec.lock" 2>nul
    )
)
echo Done!
echo.

echo [2/8] Checking Flutter installation...
echo ----------------------------------------
flutter --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Flutter is not installed!
    pause
    exit
)
echo Flutter is installed!
echo.

echo [3/8] Installing Melos...
echo ----------------------------------------
call dart pub global activate melos
echo Done!
echo.

echo [4/8] Running Melos Bootstrap...
echo ----------------------------------------
call melos bootstrap
if %errorlevel% neq 0 (
    echo Warning: Melos bootstrap had issues, trying again...
    call melos bs
)
echo Done!
echo.

echo [5/8] Fixing Core Package...
echo ----------------------------------------
cd packages\core
call flutter pub get
if %errorlevel% neq 0 (
    echo Trying flutter clean first...
    call flutter clean
    call flutter pub get
)
echo Running build_runner...
call flutter pub run build_runner build --delete-conflicting-outputs
cd ..\..
echo Done!
echo.

echo [6/8] Fixing Admin Panel...
echo ----------------------------------------
if exist "apps\admin_panel" (
    cd apps\admin_panel
    call flutter pub get
    cd ..\..
)
echo Done!
echo.

echo [7/8] Fixing Customer App...
echo ----------------------------------------
if exist "apps\customer_app" (
    cd apps\customer_app
    call flutter pub get
    cd ..\..
)
echo Done!
echo.

echo [8/8] Fixing POS and Driver Apps...
echo ----------------------------------------
if exist "apps\pos_app" (
    cd apps\pos_app
    call flutter pub get
    cd ..\..
)
if exist "apps\driver_app" (
    cd apps\driver_app
    call flutter pub get
    cd ..\..
)
echo Done!
echo.

echo Applying Dart fixes...
call dart fix --apply

echo.
echo ========================================================
echo     CHECKING RESULTS...
echo ========================================================
echo.

call flutter analyze > check.txt 2>&1
findstr /c:"No issues found" check.txt >nul
if %errorlevel% equ 0 (
    echo SUCCESS! No errors found!
    del check.txt
) else (
    findstr /c:"error" check.txt | find /c /v "" > count.txt
    set /p errors=<count.txt
    echo Still have some errors. Check with: flutter analyze
    del count.txt
    del check.txt
)

echo.
echo ========================================================
echo     NEXT STEPS:
echo ========================================================
echo.
echo 1. Run: flutter analyze
echo    To see if there are remaining errors
echo.
echo 2. If errors remain, run: dart fix --apply
echo    Again to fix more issues
echo.
echo 3. To run apps:
echo    cd apps\admin_panel    and run: flutter run
echo    cd apps\customer_app   and run: flutter run
echo    cd apps\pos_app        and run: flutter run
echo    cd apps\driver_app     and run: flutter run
echo.
echo ========================================================
echo.
pause
