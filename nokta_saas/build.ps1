# Nokta POS - PowerShell Build Script
# Run with: .\build.ps1

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "   Nokta POS Build Script" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Check Flutter
Write-Host "Checking Flutter installation..." -ForegroundColor Yellow
flutter --version
if ($LASTEXITCODE -ne 0) {
    Write-Host "Flutter is not installed or not in PATH!" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Flutter is installed" -ForegroundColor Green

# Check Dart
Write-Host "Checking Dart installation..." -ForegroundColor Yellow
dart --version
if ($LASTEXITCODE -ne 0) {
    Write-Host "Dart is not installed!" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Dart is installed" -ForegroundColor Green

# Install Melos
Write-Host "`nInstalling Melos..." -ForegroundColor Yellow
dart pub global activate melos
Write-Host "✓ Melos installed" -ForegroundColor Green

# Bootstrap project
Write-Host "`nBootstrapping project with Melos..." -ForegroundColor Yellow
melos bootstrap
if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to bootstrap project!" -ForegroundColor Red
    exit 1
}
Write-Host "✓ Project bootstrapped successfully" -ForegroundColor Green

# Get dependencies for each app
Write-Host "`nGetting dependencies for apps..." -ForegroundColor Yellow

$apps = @("pos_app", "customer_app", "driver_app", "admin_panel")
foreach ($app in $apps) {
    Write-Host "  - $app" -ForegroundColor Cyan
    Push-Location "apps\$app"
    flutter pub get
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Failed to get dependencies for $app!" -ForegroundColor Red
        Pop-Location
        exit 1
    }
    Pop-Location
}
Write-Host "✓ All dependencies installed" -ForegroundColor Green

# Get dependencies for core package
Write-Host "`nGetting dependencies for core package..." -ForegroundColor Yellow
Push-Location "packages\core"
flutter pub get
if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to get dependencies for core package!" -ForegroundColor Red
    Pop-Location
    exit 1
}
Pop-Location
Write-Host "✓ Core package dependencies installed" -ForegroundColor Green

# Run code generation (if needed)
Write-Host "`nRunning code generation..." -ForegroundColor Yellow
Push-Location "packages\core"
flutter pub run build_runner build --delete-conflicting-outputs
Pop-Location
Write-Host "✓ Code generation complete" -ForegroundColor Green

# Analyze code
Write-Host "`nAnalyzing code..." -ForegroundColor Yellow
melos run analyze
Write-Host "✓ Code analysis complete" -ForegroundColor Green

Write-Host ""
Write-Host "=====================================" -ForegroundColor Green
Write-Host "   Build completed successfully!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""
Write-Host "You can now run the apps:" -ForegroundColor Cyan
Write-Host "  cd apps\pos_app && flutter run" -ForegroundColor White
Write-Host "  cd apps\customer_app && flutter run" -ForegroundColor White
Write-Host "  cd apps\driver_app && flutter run" -ForegroundColor White
Write-Host "  cd apps\admin_panel && flutter run" -ForegroundColor White
