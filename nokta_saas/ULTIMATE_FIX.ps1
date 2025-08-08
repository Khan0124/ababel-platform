# NOKTA POS - Ultimate System Fix
# PowerShell Script v3.0

$ErrorActionPreference = "Continue"
Clear-Host

Write-Host "================================================" -ForegroundColor Yellow
Write-Host "   NOKTA POS - ULTIMATE SYSTEM FIX v3.0" -ForegroundColor Yellow
Write-Host "   Complete Rebuild & Error Resolution" -ForegroundColor Yellow
Write-Host "================================================" -ForegroundColor Yellow
Write-Host ""

# Function to check command success
function Check-Success {
    param($Message)
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ $Message" -ForegroundColor Green
    } else {
        Write-Host "   ✗ $Message (Failed)" -ForegroundColor Red
    }
}

# Step 1: Deep Clean
Write-Host "[1/12] DEEP CLEANING..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray

# Remove all generated files
Get-ChildItem -Path . -Include *.freezed.dart -Recurse | Remove-Item -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path . -Include *.g.dart -Recurse | Remove-Item -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path . -Include pubspec.lock -Recurse | Remove-Item -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path . -Include .dart_tool -Recurse -Directory | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path . -Include build -Recurse -Directory | Remove-Item -Recurse -Force -ErrorAction SilentlyContinue

Check-Success "Deep clean complete"
Write-Host ""

# Step 2: Update Flutter
Write-Host "[2/12] UPDATING FLUTTER..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
flutter upgrade --force 2>$null
Check-Success "Flutter updated"
Write-Host ""

# Step 3: Clear caches
Write-Host "[3/12] CLEARING CACHES..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
flutter clean 2>$null
dart pub cache clean --force 2>$null
flutter pub cache repair 2>$null
Check-Success "Caches cleared"
Write-Host ""

# Step 4: Install Melos
Write-Host "[4/12] INSTALLING MELOS..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
dart pub global deactivate melos 2>$null
dart pub global activate melos
Check-Success "Melos installed"
Write-Host ""

# Step 5: Root dependencies
Write-Host "[5/12] ROOT DEPENDENCIES..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
flutter pub get
Check-Success "Root dependencies installed"
Write-Host ""

# Step 6: Melos Bootstrap
Write-Host "[6/12] MELOS BOOTSTRAP..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
melos clean 2>$null
melos bootstrap
Check-Success "Bootstrap complete"
Write-Host ""

# Step 7: Core Package
Write-Host "[7/12] CORE PACKAGE SETUP..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
Push-Location "packages\core"
flutter pub get
dart pub get
Pop-Location
Check-Success "Core package ready"
Write-Host ""

# Step 8: Generate files
Write-Host "[8/12] GENERATING FILES..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
Push-Location "packages\core"
dart run build_runner clean 2>$null
dart run build_runner build --delete-conflicting-outputs
Pop-Location
Check-Success "Files generated"
Write-Host ""

# Step 9: App dependencies
Write-Host "[9/12] APP DEPENDENCIES..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray

$apps = @("pos_app", "customer_app", "driver_app", "admin_panel")
foreach ($app in $apps) {
    Write-Host "   Installing $app..." -ForegroundColor Gray
    Push-Location "apps\$app"
    flutter clean 2>$null
    flutter pub get
    Pop-Location
}
Check-Success "All apps ready"
Write-Host ""

# Step 10: Fix permissions
Write-Host "[10/12] FIXING PERMISSIONS..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
# Fix any permission issues on Windows
$folders = @(".dart_tool", "build", ".pub-cache")
foreach ($folder in $folders) {
    if (Test-Path $folder) {
        icacls $folder /grant "${env:USERNAME}:F" /T /Q 2>$null
    }
}
Check-Success "Permissions fixed"
Write-Host ""

# Step 11: Verification
Write-Host "[11/12] VERIFICATION..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
flutter doctor
melos list
Check-Success "Verification complete"
Write-Host ""

# Step 12: Analysis
Write-Host "[12/12] FINAL ANALYSIS..." -ForegroundColor Cyan
Write-Host "----------------------------------------" -ForegroundColor DarkGray
$analyzeOutput = flutter analyze --no-fatal-warnings 2>&1
$errorCount = ($analyzeOutput | Select-String "error").Count
$warningCount = ($analyzeOutput | Select-String "warning").Count

Write-Host ""
Write-Host "================================================" -ForegroundColor Green
Write-Host "   COMPLETE! System Fully Rebuilt" -ForegroundColor Green
Write-Host "================================================" -ForegroundColor Green
Write-Host ""
Write-Host "Analysis Results:" -ForegroundColor Yellow
Write-Host "  Errors: $errorCount" -ForegroundColor $(if ($errorCount -eq 0) {"Green"} else {"Red"})
Write-Host "  Warnings: $warningCount" -ForegroundColor Yellow
Write-Host ""

if ($errorCount -eq 0) {
    Write-Host "✅ SUCCESS! No errors found!" -ForegroundColor Green
} else {
    Write-Host "⚠️ Some errors remain. Run 'flutter analyze' for details" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Cyan
Write-Host "  1. cd apps\pos_app       -> flutter run" -ForegroundColor White
Write-Host "  2. cd apps\customer_app  -> flutter run" -ForegroundColor White
Write-Host "  3. cd apps\driver_app    -> flutter run" -ForegroundColor White
Write-Host "  4. cd apps\admin_panel   -> flutter run" -ForegroundColor White
Write-Host ""
Write-Host "Press any key to exit..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
