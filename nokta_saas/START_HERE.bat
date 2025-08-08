@echo off
cls
echo ================================================
echo       حذف X.bat وإنشاء حلول جديدة بسيطة
echo ================================================
echo.

cd /d C:\flutter_projects\nokta_saas

:: Delete all old complex files
del X.bat 2>nul
del CLEANUP.bat 2>nul

echo تم حذف X.bat
echo.
echo الملفات الجديدة البسيطة المتاحة:
echo ================================
echo.
echo 1.bat      - الأبسط (8 أسطر فقط)
echo RUN.bat    - بسيط (6 خطوات)
echo SIMPLE.bat - أساسي
echo FIX.bat    - مفصل
echo WORK.bat   - شامل
echo.
echo جرب 1.bat أولاً - هو الأسهل!
echo.
echo للتشغيل اكتب: 1.bat
echo.
pause
