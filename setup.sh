#!/bin/bash
# Quick Setup Script for MediCare

echo "🏥 MediCare Setup Script"
echo "========================"

# Check if MySQL is running
if ! command -v mysql &> /dev/null; then
    echo "❌ MySQL not found. Please install MySQL first."
    exit 1
fi

echo "✅ Setting up database..."

# Create database and import data
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS medicare;"
mysql -u root -p medicare < "medicare (3).sql"
mysql -u root -p medicare < "blood_bank.sql"

echo "✅ Database setup complete!"
echo ""
echo "🚀 Next Steps:"
echo "1. Update config.php with your database credentials"
echo "2. Start your web server (Apache/Nginx)"
echo "3. Open home.html in browser"
echo ""
echo "🌐 Access your site at: http://localhost/med/home.html"
