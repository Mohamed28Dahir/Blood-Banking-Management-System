<?php
/**
 * Blood Donation Management System
 * User Profile & Notifications
 */

$page_title = "My Profile";
include '../includes/header.php';

requireUser();
requireApproved();

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch notifications
$notif_query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($notif_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();

// Mark notifications as read
$conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    $update_query = "UPDATE users SET phone = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $phone, $address, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        redirect('profile.php');
    }
    $stmt->close();
}
?>

<div class="container page-container">
    <div class="row">
        <!-- Profile Card -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user"></i> Profile Information
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x"></i>
                        </div>
                    </div>
                    <h4 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p class="text-muted mb-3">
                        <span class="badge <?php echo getStatusBadge($user['status']); ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </p>
                    <hr>
                    <div class="text-start">
                        <p class="mb-2">
                            <i class="fas fa-envelope text-primary"></i> 
                            <?php echo htmlspecialchars($user['email']); ?>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-phone text-success"></i>
                            <?php echo htmlspecialchars($user['phone']); ?>
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-map-marker-alt text-danger"></i>
                            <?php echo htmlspecialchars($user['address']); ?>
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-calendar"></i>
                            Member since <?php echo formatDate($user['created_at']); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Profile & Notifications -->
        <div class="col-lg-8">
            <!-- Edit Profile Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-edit"></i> Edit Profile
                </div>
                <div class="card-body">
                    <?php echo displaySuccess(); ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" disabled>
                            <small class="text-muted">Contact admin to change your name</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="text-muted">Contact admin to change your email</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Notifications -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bell"></i> All Notifications
                </div>
                <div class="card-body">
                    <?php if ($notifications->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while($notif = $notifications->fetch_assoc()): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div>
                                            <span class="badge <?php echo getStatusBadge($notif['type']); ?> mb-2">
                                                <?php echo ucfirst($notif['type']); ?>
                                            </span>
                                            <p class="mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                        </div>
                                        <small class="text-muted text-nowrap ms-3">
                                            <?php echo formatDateTime($notif['created_at']); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            No notifications yet
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
