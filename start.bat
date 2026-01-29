@echo off
echo 🏥 MediCare Docker Setup - Windows
echo ===================================
echo Created by: Arun Jadhav, Yogesh Bhore & Prathviraj Bagli
echo.

REM Check if Docker Desktop is running
docker version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Desktop is not running!
    echo.
    echo 🔧 Please:
    echo 1. Start Docker Desktop
    echo 2. Wait for it to fully load
    echo 3. Run this script again
    echo.
    pause
    exit /b 1
)

echo ✅ Docker Desktop is running!
echo 🚀 Starting MediCare application...
echo.

REM Stop any existing containers
docker-compose down

REM Build and start containers
docker-compose up -d --build

if %errorlevel% equ 0 (
    echo.
    echo 🎉 MediCare is now running!
    echo.
    echo 📱 Access your application:
    echo    🌐 Main Website: http://localhost:8080/home.html
    echo    🩸 Blood Bank: http://localhost:8080/blood_bank.html
    echo    🛠️  Admin Panel: http://localhost:8080/blood_admin.php
    echo    💾 Database Admin: http://localhost:8081
    echo.
    echo 🔑 Database Credentials:
    echo    Username: medicare_user
    echo    Password: medicare_pass
    echo    Database: medicare
    echo.
    echo 🛑 To stop: docker-compose down
    echo.
    echo Press any key to open the website...
    pause >nul
    start http://localhost:8080/home.html
) else (
    echo ❌ Failed to start containers. Check Docker Desktop and try again.
    pause
)
