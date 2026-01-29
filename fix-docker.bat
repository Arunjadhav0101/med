@echo off
echo 🔧 Docker Troubleshoot Script
echo =============================

echo Step 1: Checking Docker Desktop...
docker version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Docker Desktop not running. Please start it first.
    echo 1. Open Docker Desktop
    echo 2. Wait for green whale icon
    echo 3. Run this script again
    pause
    exit /b 1
)

echo ✅ Docker is running!

echo Step 2: Cleaning up...
docker-compose down
docker system prune -f

echo Step 3: Pulling images...
docker pull mysql:8.0
docker pull php:8.1-apache  
docker pull phpmyadmin/phpmyadmin

echo Step 4: Starting containers...
docker-compose up -d --build

echo Step 5: Checking status...
docker ps

echo.
echo 🎉 If you see 3 containers running, success!
echo 📱 Open: http://localhost:8080/home.html
pause
