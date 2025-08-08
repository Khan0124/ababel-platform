@echo off
REM Generate Freezed and JsonSerializable files

echo.
echo =====================================
echo   Generating Freezed Files
echo =====================================
echo.

cd packages\core

REM Clean old generated files
echo Cleaning old generated files...
del /S *.freezed.dart 2>nul
del /S *.g.dart 2>nul

REM Get dependencies
echo Getting dependencies...
flutter pub get

REM Run build_runner
echo Running build_runner...
flutter pub run build_runner build --delete-conflicting-outputs

echo.
echo Generation complete!

REM Return to root
cd ..\..

echo.
echo =====================================
echo   Generated files created successfully!
echo =====================================
echo.
pause
