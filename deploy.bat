@echo off
REM Production Deployment Script for Windows

echo ========================================
echo   Production Deployment Preparation
echo ========================================
echo.

REM Create deployment directory
if not exist deploy mkdir deploy

REM Copy frontend files
echo [1/5] Copying frontend files...
xcopy /E /I /Y frontend deploy\frontend

REM Copy API files
echo [2/5] Copying API files...
xcopy /E /I /Y api deploy\api

REM Copy backend files
echo [3/5] Copying backend files...
xcopy /E /I /Y backend deploy\backend

REM Copy root files
echo [4/5] Copying root files...
copy /Y .htaccess deploy\ 2>nul
copy /Y index.php deploy\ 2>nul

REM Clean development files
echo [5/5] Cleaning development files...
del /F /Q deploy\*.md 2>nul
del /F /Q deploy\frontend\README.md 2>nul

echo.
echo ========================================
echo   Deployment Package Ready!
echo ========================================
echo.
echo Location: .\deploy\
echo.
echo Next Steps:
echo 1. Compress the deploy folder to deploy.zip
echo 2. Login to InfinityFree File Manager
echo 3. Upload and extract in htdocs/
echo 4. Verify backend\config.php credentials
echo.
echo Access URL: https://hcthegreat.ct.ws/
echo.
pause
