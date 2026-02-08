<?php
/**
 * Blood Donation Management System
 * Manage Users - Admin Panel
 */

$page_title = "Manage Users";
$include_admin_css = true;
include '../includes/header.php';

requireAdmin();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $conn->query("UPDATE users SET status = 'approved' WHERE id = $user_id");
        createNotification($user_id, "Your account has been approved! You can now access the system.", "approval");
        $_SESSION['success'] = "User approved successfully!";
    } elseif ($action == 'block') {
        $conn->query("UPDATE users SET status = 'blocked' WHERE id = $user_id");
        createNotification($user_id, "Your account has been blocked. Please contact the administrator.", "rejection");
        $_SESSION['success'] = "User blocked successfully!";
    } elseif ($action == 'delete') {
        $conn->query("DELETE FROM users WHERE id = $user_id");
        $_SESSION['success'] = "User deleted successfully!";
    }
    
    redirect('manage_users.php');
}

// Fetch users with filters
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

$query = "SELECT * FROM users WHERE role = 'user'";

if ($status_filter != 'all') {
    $query .= " AND status = '$status_filter'";
}

if (!empty($search)) {
    $search_term = escapeLike($search);
    $query .= " AND (full_name LIKE '%$search_term%' OR email LIKE '%$search_term%')";
}

$query .= " ORDER BY created_at DESC";
$users = $conn->query($query);
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-users"></i> Manage Users</h1>
                <p class="text-muted mb-0">Approve, block, or delete user accounts</p>
            </div>

            <?php echo displaySuccess(); ?>
            <?php echo displayError(); ?>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="blocked" <?php echo $status_filter == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="manage_users.php" class="btn btn-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="data-table-container">
                <div class="table-responsive">
                    <table class="table table-hover" id="usersTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while($user = $users->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($user['address'], 0, 30)) . '...'; ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadge($user['status']); ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($user['status'] == 'pending'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($user['status'] != 'blocked'): ?>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="action" value="block">
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Block" onclick="return confirm('Block this user?')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirmDelete('Delete this user permanently?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$hide_navbar = true;
include '../includes/footer.php';
?>
