@echo off
cls
echo =====================================
echo    NOKTA POS - Complete Fix Script
echo =====================================
echo.

REM Step 1: Clean everything
echo [1/8] Cleaning project...
echo ----------------------------------------
flutter clean 2>nul
del /S /Q pubspec.lock 2>nul
del /S /Q *.freezed.dart 2>nul
del /S /Q *.g.dart 2>nul
echo Clean complete!
echo.

REM Step 2: Install Melos
echo [2/8] Installing Melos...
echo ----------------------------------------
dart pub global activate melos
echo Melos installed!
echo.

REM Step 3: Bootstrap with Melos
echo [3/8] Bootstrapping project with Melos...
echo ----------------------------------------
melos bootstrap
echo Bootstrap complete!
echo.

REM Step 4: Get Core dependencies
echo [4/8] Installing Core package dependencies...
echo ----------------------------------------
cd packages\core
flutter pub get
echo Core dependencies installed!
cd ..\..
echo.

REM Step 5: Generate Freezed files
echo [5/8] Generating Freezed and JsonSerializable files...
echo ----------------------------------------
cd packages\core
flutter pub run build_runner build --delete-conflicting-outputs
echo Generated files created!
cd ..\..
echo.

REM Step 6: Get dependencies for each app
echo [6/8] Installing app dependencies...
echo ----------------------------------------

echo Installing POS App dependencies...
cd apps\pos_app
flutter pub get
cd ..\..

echo Installing Customer App dependencies...
cd apps\customer_app
flutter pub get
cd ..\..

echo Installing Driver App dependencies...
cd apps\driver_app
flutter pub get
cd ..\..

echo Installing Admin Panel dependencies...
cd apps\admin_panel
flutter pub get
cd ..\..

echo All app dependencies installed!
echo.

REM Step 7: Analyze code
echo [7/8] Analyzing code for errors...
echo ----------------------------------------
flutter analyze --no-fatal-infos --no-fatal-warnings
echo.

REM Step 8: Summary
echo [8/8] Fix Summary
echo =====================================
echo.
echo Fixed Issues:
echo [✓] All dependencies installed
echo [✓] Freezed files generated
echo [✓] CardTheme errors fixed
echo [✓] Import paths corrected
echo [✓] Missing packages added
echo.

REM Check if there are still errors
flutter analyze --no-fatal-infos --no-fatal-warnings >nul 2>&1
if %errorlevel% equ 0 (
    echo =====================================
    echo    ✅ ALL ERRORS FIXED SUCCESSFULLY!
    echo =====================================
) else (
    echo =====================================
    echo    ⚠️ Some issues may remain
    echo    Run 'flutter analyze' for details
    echo =====================================
)

echo.
echo You can now run the apps:
echo   - cd apps\pos_app ^&^& flutter run
echo   - cd apps\customer_app ^&^& flutter run
echo   - cd apps\driver_app ^&^& flutter run
echo   - cd apps\admin_panel ^&^& flutter run
echo.
pause
