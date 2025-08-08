@echo off
REM ====================================
REM Nokta POS System Deployment Script (Windows)
REM Version: 1.0.0
REM ====================================

echo.
echo ====================================
echo Starting Nokta POS Deployment...
echo ====================================
echo.

REM Check Flutter
where flutter >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Flutter is not installed!
    pause
    exit /b 1
)
echo [OK] Flutter found

REM Check Node.js
where node >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Node.js is not installed!
    pause
    exit /b 1
)
echo [OK] Node.js found

REM Check Docker
where docker >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Docker is not installed!
    pause
    exit /b 1
)
echo [OK] Docker found

REM Step 1: Setup Flutter Dependencies
echo.
echo Setting up Flutter dependencies...
call dart pub global activate melos
call melos bootstrap
echo [OK] Flutter dependencies installed

REM Step 2: Build Flutter Apps
echo.
echo Building Flutter applications...

REM Build POS App
echo Building POS App...
cd apps\pos_app
call flutter build windows --release
call flutter build web --release
cd ..\..
echo [OK] POS App built

REM Build Customer App
echo Building Customer App...
cd apps\customer_app
call flutter build web --release
cd ..\..
echo [OK] Customer App built

REM Build Admin Panel
echo Building Admin Panel...
cd apps\admin_panel
call flutter build web --release
cd ..\..
echo [OK] Admin Panel built

REM Step 3: Setup Backend
echo.
echo Setting up Backend...
cd backend
call npm install
cd ..
echo [OK] Backend dependencies installed

REM Step 4: Start Docker Services
echo.
echo Starting Docker services...
docker-compose up -d
echo [OK] All services started

REM Wait for services
echo.
echo Waiting for services to be ready...
timeout /t 15 /nobreak > nul

REM Check services status
echo.
echo Checking services status...
docker-compose ps

REM Print success message
echo.
echo ======================================
echo    Nokta POS System Deployed Successfully!
echo ======================================
echo.
echo Access URLs:
echo    Admin Panel: http://localhost/admin
echo    Customer App: http://localhost
echo    Backend API: http://localhost:3001
echo    phpMyAdmin: http://localhost:8080
echo.
echo Windows App:
echo    POS App: apps\pos_app\build\windows\x64\runner\Release\
echo.
echo Web Apps:
echo    Admin: apps\admin_panel\build\web\
echo    Customer: apps\customer_app\build\web\
echo.
echo Default Credentials:
echo    Admin: admin / admin123
echo    MySQL: root / nokta_root_2024
echo.
echo ======================================
echo.
pause
