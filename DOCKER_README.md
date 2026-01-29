# 🐳 MediCare Docker Setup

## Single Command Installation

### Prerequisites
- Docker installed
- Docker Compose installed

### 🚀 Run Everything with One Command:

```bash
./start.sh
```

### 📱 Access Points:
- **Main Website**: http://localhost:8080/home.html
- **Blood Bank**: http://localhost:8080/blood_bank.html  
- **Admin Panel**: http://localhost:8080/blood_admin.php
- **Database Admin**: http://localhost:8081 (phpMyAdmin)

### 🔧 Manual Docker Commands:

```bash
# Start all services
docker-compose up -d

# Stop all services  
docker-compose down

# View logs
docker-compose logs

# Rebuild containers
docker-compose up -d --build
```

### 🏗️ What Gets Created:
- **MySQL Database** with sample data
- **PHP Apache Web Server** 
- **phpMyAdmin** for database management
- **Automatic database initialization**

### 🔑 Database Access:
- **Host**: localhost:3306
- **Username**: medicare_user
- **Password**: medicare_pass
- **Database**: medicare

### 📊 Container Status:
```bash
docker ps
```

### 🛑 Stop Everything:
```bash
docker-compose down
```
