<?php
/**
 * Blood Donation Management System
 * Manage Donors - Admin Panel
 */

$page_title = "Manage Donors";
$include_admin_css = true;
include '../includes/header.php';

requireAdmin();

// Handle donor actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $donor_id = (int)$_POST['donor_id'];
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $blood_group = $_POST['blood_group'];
        $admin_notes = sanitize($_POST['admin_notes']);
        
        $conn->query("UPDATE donors SET status = 'approved', admin_notes = '$admin_notes' WHERE id = $donor_id");
        
        // Update blood stock +1 unit when donor approved
        $conn->query("UPDATE blood_stock SET units_available = units_available + 1 WHERE blood_group = '$blood_group'");
        
        createNotification($user_id, "Your donor application has been approved! Thank you for your contribution to saving lives.", "approval");
        $_SESSION['success'] = "Donor application approved successfully!";
    } elseif ($action == 'reject') {
        $admin_notes = sanitize($_POST['admin_notes']);
        $conn->query("UPDATE donors SET status = 'rejected', admin_notes = '$admin_notes' WHERE id = $donor_id");
        createNotification($user_id, "Your donor application has been rejected. Reason: $admin_notes", "rejection");
        $_SESSION['success'] = "Donor application rejected.";
    }
    
    redirect('manage_donors.php');
}

// Fetch donors with filters
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'pending';
$blood_filter = isset($_GET['blood_group']) ? sanitize($_GET['blood_group']) : 'all';

$query = "SELECT d.*, u.full_name, u.email, u.phone FROM donors d JOIN users u ON d.user_id = u.id WHERE 1=1";

if ($status_filter != 'all') {
    $query .= " AND d.status = '$status_filter'";
}

if ($blood_filter != 'all') {
    $query .= " AND d.blood_group = '$blood_filter'";
}

$query .= " ORDER BY d.created_at DESC";
$donors = $conn->query($query);
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-hand-holding-heart"></i> Manage Donors</h1>
                <p class="text-muted mb-0">Verify and approve donor applications</p>
            </div>

            <?php echo displaySuccess(); ?>
            <?php echo displayError(); ?>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="blood_group">
                            <option value="all">All Blood Groups</option>
                            <?php foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                                <option value="<?php echo $bg; ?>" <?php echo $blood_filter == $bg ? 'selected' : ''; ?>><?php echo $bg; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="manage_donors.php" class="btn btn-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Donors Table -->
            <div class="data-table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Donor Name</th>
                                <th>Blood Group</th>
                                <th>Age</th>
                                <th>Weight</th>
                                <th>Medical Proof</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while($donor = $donors->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($donor['full_name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($donor['email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getBloodGroupBadge($donor['blood_group']); ?>">
                                            <?php echo $donor['blood_group']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $donor['age']; ?> years</td>
                                    <td><?php echo $donor['weight']; ?> kg</td>
                                    <td>
                                        <a href="download_file.php?file=<?php echo urlencode($donor['medical_proof']); ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> View
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadge($donor['status']); ?>">
                                            <?php echo ucfirst($donor['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($donor['created_at']); ?></td>
                                    <td>
                                        <?php if ($donor['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal<?php echo $donor['id']; ?>">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $donor['id']; ?>">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                            
                                            <!-- Approve Modal -->
                                            <div class="modal fade" id="approveModal<?php echo $donor['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title">Approve Donor Application</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="donor_id" value="<?php echo $donor['id']; ?>">
                                                                <input type="hidden" name="user_id" value="<?php echo $donor['user_id']; ?>">
                                                                <input type="hidden" name="blood_group" value="<?php echo $donor['blood_group']; ?>">
                                                                <input type="hidden" name="action" value="approve">
                                                                
                                                                <p><strong>Donor:</strong> <?php echo htmlspecialchars($donor['full_name']); ?></p>
                                                                <p><strong>Blood Group:</strong> <?php echo $donor['blood_group']; ?></p>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Admin Notes (optional)</label>
                                                                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Add any notes or comments..."></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-success">Approve Application</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?php echo $donor['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Reject Donor Application</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="donor_id" value="<?php echo $donor['id']; ?>">
                                                                <input type="hidden" name="user_id" value="<?php echo $donor['user_id']; ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                
                                                                <p><strong>Donor:</strong> <?php echo htmlspecialchars($donor['full_name']); ?></p>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Reason for Rejection *</label>
                                                                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Explain why this application is being rejected..." required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Reject Application</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <small class="text-muted">
                                                <?php echo $donor['admin_notes'] ?: 'No notes'; ?>
                                            </small>
                                        <?php endif; ?>
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
