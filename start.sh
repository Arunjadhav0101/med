#!/bin/bash

echo "🏥 MediCare Docker Setup"
echo "========================"
echo "Created by: Arun Jadhav, Yogesh Bhore & Prathviraj Bagli"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker not found. Please install Docker first."
    echo "Visit: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose not found. Please install Docker Compose first."
    exit 1
fi

echo "✅ Docker found!"
echo "🚀 Starting MediCare application..."
echo ""

# Stop any existing containers
docker-compose down

# Build and start containers
docker-compose up -d --build

echo ""
echo "🎉 MediCare is now running!"
echo ""
echo "📱 Access your application:"
echo "   🌐 Main Website: http://localhost:8080/home.html"
echo "   🩸 Blood Bank: http://localhost:8080/blood_bank.html"
echo "   🛠️  Admin Panel: http://localhost:8080/blood_admin.php"
echo "   💾 Database Admin: http://localhost:8081 (phpMyAdmin)"
echo ""
echo "🔑 Database Credentials:"
echo "   Host: localhost:3306"
echo "   Username: medicare_user"
echo "   Password: medicare_pass"
echo "   Database: medicare"
echo ""
echo "🛑 To stop: docker-compose down"
echo "📊 To view logs: docker-compose logs"
