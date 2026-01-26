<style>
/* Admin Dashboard Styles */
.admin-dashboard {
    min-height: 100vh;
    background: #f5f7fa;
}

.admin-sidebar {
    width: 250px;
    background: #2c3e50;
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    transition: all 0.3s;
}

.admin-main {
    margin-left: 250px;
    padding: 20px;
}

.admin-header {
    background: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.logo-area {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-logo {
    font-size: 24px;
    font-weight: 700;
    color: #2c3e50;
}

.welcome-text {
    color: #666;
}

.admin-user {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.sidebar-header {
    padding: 30px 20px;
    background: #1a252f;
}

.admin-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 5px;
}

.admin-subtitle {
    font-size: 12px;
    opacity: 0.7;
}

.sidebar-menu {
    padding: 20px 0;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 20px;
    color: #bdc3c7;
    text-decoration: none;
    transition: all 0.3s;
    border-left: 4px solid transparent;
    position: relative;
}

.menu-item:hover, .menu-item.active {
    background: #34495e;
    color: white;
    border-left-color: #3498db;
}

.menu-item i {
    width: 20px;
    text-align: center;
}

/* ... (include other styles from admin_dashboard.php) ... */
</style>