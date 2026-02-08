<!-- Admin Sidebar Navigation -->
<div class="col-md-3 col-lg-2 p-0 admin-sidebar">
    <div class="text-white text-center py-4">
        <h4><i class="fas fa-user-shield"></i> Admin Panel</h4>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>" href="manage_users.php">
            <i class="fas fa-users"></i> Manage Users
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_donors.php' ? 'active' : ''; ?>" href="manage_donors.php">
            <i class="fas fa-hand-holding-heart"></i> Manage Donors
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_requests.php' ? 'active' : ''; ?>" href="manage_requests.php">
            <i class="fas fa-notes-medical"></i> Manage Requests
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_blood_stock.php' ? 'active' : ''; ?>" href="manage_blood_stock.php">
            <i class="fas fa-database"></i> Blood Stock
        </a>
        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
            <i class="fas fa-file-export"></i> Reports
        </a>
        <hr style="border-color: rgba(255,255,255,0.2);">
        <a class="nav-link" href="../logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</div>
