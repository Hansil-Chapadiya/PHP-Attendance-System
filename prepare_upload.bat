@echo off
echo ========================================
echo  InfinityFree Deployment Package Creator
echo ========================================
echo.

set DEPLOY_DIR=infinityfree_upload
set TIMESTAMP=%date:~-4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%

echo Creating fresh deployment package...
if exist %DEPLOY_DIR% rmdir /s /q %DEPLOY_DIR%
mkdir %DEPLOY_DIR%

echo.
echo Copying files from deploy folder...
xcopy /E /I /Y deploy %DEPLOY_DIR%

echo.
echo ✅ Deployment package ready in: %DEPLOY_DIR%
echo.
echo 📤 UPLOAD TO INFINITYFREE:
echo    Upload ALL files from '%DEPLOY_DIR%' to 'htdocs/' on InfinityFree
echo.
echo 🔑 IMPORTANT FILES UPDATED:
echo    - backend/helpers.php (UTF-8 fixed!)
echo    - All API endpoints
echo    - Frontend files
echo.
echo 💾 Test Credentials:
echo    Student: student1 / Pass@123
echo    Faculty: faculty1 / Pass@123
echo.
pause
