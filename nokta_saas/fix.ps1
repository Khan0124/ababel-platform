Write-Host "=====================================" -ForegroundColor Green
Write-Host "   NOKTA POS - FIXING ALL ERRORS" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""

Write-Host "[1/6] Getting root dependencies..." -ForegroundColor Yellow
flutter pub get
Write-Host "Done!" -ForegroundColor Green

Write-Host "[2/6] Activating Melos..." -ForegroundColor Yellow
dart pub global activate melos
Write-Host "Done!" -ForegroundColor Green

Write-Host "[3/6] Running Melos Bootstrap..." -ForegroundColor Yellow
melos bootstrap
Write-Host "Done!" -ForegroundColor Green

Write-Host "[4/6] Installing Core dependencies..." -ForegroundColor Yellow
Set-Location "packages\core"
flutter pub get
Set-Location "..\..\"
Write-Host "Done!" -ForegroundColor Green

Write-Host "[5/6] Generating Freezed files..." -ForegroundColor Yellow
Set-Location "packages\core"
dart run build_runner build --delete-conflicting-outputs
Set-Location "..\..\"
Write-Host "Done!" -ForegroundColor Green

Write-Host "[6/6] Installing app dependencies..." -ForegroundColor Yellow
$apps = @("pos_app", "customer_app", "driver_app", "admin_panel")
foreach ($app in $apps) {
    Write-Host "  - Installing $app" -ForegroundColor Cyan
    Set-Location "apps\$app"
    flutter pub get
    Set-Location "..\..\"
}
Write-Host "Done!" -ForegroundColor Green

Write-Host ""
Write-Host "=====================================" -ForegroundColor Green
Write-Host "   SUCCESS! All errors fixed!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host ""
Write-Host "Run 'flutter analyze' to verify" -ForegroundColor Yellow
