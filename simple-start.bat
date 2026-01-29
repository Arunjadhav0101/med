@echo off
echo 🏥 MediCare - Simple Local Setup (No Docker)
echo ===============================================
echo Created by: Arun Jadhav, Yogesh Bhore & Prathviraj Bagli
echo.

echo This will start a simple PHP server for testing
echo.

REM Check if PHP is installed
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP not found. 
    echo.
    echo 📥 Quick Options:
    echo 1. Install XAMPP: https://www.apachefriends.org/
    echo 2. Or install PHP: https://windows.php.net/download/
    echo.
    pause
    exit /b 1
)

echo ✅ PHP found!
echo 🚀 Starting MediCare on http://localhost:8000
echo.
echo 📱 Access Points:
echo    🌐 Main Website: http://localhost:8000/home.html
echo    🩸 Blood Bank: http://localhost:8000/blood_bank.html
echo.
echo ⚠️  Note: Database features need XAMPP/MySQL setup
echo.
echo Press Ctrl+C to stop the server
echo.

REM Start PHP built-in server
php -S localhost:8000
