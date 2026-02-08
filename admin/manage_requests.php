<?php
/**
 * Blood Donation Management System
 * Manage Blood Requests - Admin Panel
 */

$page_title = "Manage Blood Requests";
$include_admin_css = true;
include '../includes/header.php';

requireAdmin();

// Handle request actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = (int)$_POST['request_id'];
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action == 'approve') {
        $blood_group = $_POST['blood_group'];
        $units_needed = (int)$_POST['units_needed'];
        $admin_notes = sanitize($_POST['admin_notes']);
        
        // Check blood stock
        $stock_result = $conn->query("SELECT units_available FROM blood_stock WHERE blood_group = '$blood_group'");
        $stock = $stock_result->fetch_assoc();
        
        if ($stock['units_available'] >= $units_needed) {
            $conn->query("UPDATE blood_requests SET status = 'approved', admin_notes = '$admin_notes' WHERE id = $request_id");
            
            // Deduct from blood stock
            $conn->query("UPDATE blood_stock SET units_available = units_available - $units_needed WHERE blood_group = '$blood_group'");
            
            createNotification($user_id, "Your blood request has been approved. Blood units are available.", "approval");
            $_SESSION['success'] = "Blood request approved successfully! Stock updated.";
        } else {
            $_SESSION['error'] = "Insufficient blood stock. Available: {$stock['units_available']}, Needed: $units_needed";
        }
    } elseif ($action == 'reject') {
        $admin_notes = sanitize($_POST['admin_notes']);
        $conn->query("UPDATE blood_requests SET status = 'rejected', admin_notes = '$admin_notes' WHERE id = $request_id");
        createNotification($user_id, "Your blood request has been rejected. Reason: $admin_notes", "rejection");
        $_SESSION['success'] = "Blood request rejected.";
    } elseif ($action == 'fulfill') {
        $conn->query("UPDATE blood_requests SET status = 'fulfilled' WHERE id = $request_id");
        createNotification($user_id, "Your blood request has been marked as fulfilled.", "info");
        $_SESSION['success'] = "Blood request marked as fulfilled.";
    }
    
    redirect('manage_requests.php');
}

// Fetch requests with filters
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'pending';
$urgency_filter = isset($_GET['urgency']) ? sanitize($_GET['urgency']) : 'all';
$blood_filter = isset($_GET['blood_group']) ? sanitize($_GET['blood_group']) : 'all';

$query = "SELECT br.*, u.full_name, u.email, u.phone FROM blood_requests br JOIN users u ON br.user_id = u.id WHERE 1=1";

if ($status_filter != 'all') {
    $query .= " AND br.status = '$status_filter'";
}

if ($urgency_filter != 'all') {
    $query .= " AND br.urgency = '$urgency_filter'";
}

if ($blood_filter != 'all') {
    $query .= " AND br.blood_group = '$blood_filter'";
}

$query .= " ORDER BY 
    CASE br.urgency
        WHEN 'critical' THEN 1
        WHEN 'high' THEN 2
        WHEN 'medium' THEN 3
        WHEN 'low' THEN 4
    END,
    br.created_at DESC";
$requests = $conn->query($query);

// Fetch current blood stock for reference
$blood_stock = $conn->query("SELECT * FROM blood_stock ORDER BY blood_group");
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-notes-medical"></i> Manage Blood Requests</h1>
                <p class="text-muted mb-0">Review and approve blood requests based on stock availability</p>
            </div>

            <?php echo displaySuccess(); ?>
            <?php echo displayError(); ?>

            <!-- Current Blood Stock Info -->
            <div class="card mb-4 bg-light">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-tint"></i> Current Blood Stock:</h6>
                    <div class="row g-2">
                        <?php while($stock = $blood_stock->fetch_assoc()): ?>
                            <div class="col-6 col-md-3">
                                <strong><?php echo $stock['blood_group']; ?>:</strong>
                                <span class="badge <?php echo $stock['units_available'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $stock['units_available']; ?> units
                                </span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status_filter == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="fulfilled" <?php echo $status_filter == 'fulfilled' ? 'selected' : ''; ?>>Fulfilled</option>
                            <option value="all" <?php echo $status_filter == 'all' ? 'selected' : ''; ?>>All Status</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" name="urgency">
                            <option value="all">All Urgency</option>
                            <option value="critical" <?php echo $urgency_filter == 'critical' ? 'selected' : ''; ?>>Critical</option>
                            <option value="high" <?php echo $urgency_filter == 'high' ? 'selected' : ''; ?>>High</option>
                            <option value="medium" <?php echo $urgency_filter == 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="low" <?php echo $urgency_filter == 'low' ? 'selected' : ''; ?>>Low</option>
                        </select>
                    </div>
                    <div class="col-md-2">
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
                        <a href="manage_requests.php" class="btn btn-secondary w-100">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Requests Table -->
            <div class="data-table-container">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Requested By</th>
                                <th>Patient Name</th>
                                <th>Blood Group</th>
                                <th>Units</th>
                                <th>Hospital</th>
                                <th>Contact</th>
                                <th>Urgency</th>
                                <th>Required Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while($req = $requests->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($req['full_name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($req['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($req['patient_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getBloodGroupBadge($req['blood_group']); ?>">
                                            <?php echo $req['blood_group']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $req['units_needed']; ?></td>
                                    <td><?php echo htmlspecialchars($req['hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($req['contact_number']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getUrgencyBadge($req['urgency']); ?>">
                                            <?php echo ucfirst($req['urgency']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($req['required_date']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadge($req['status']); ?>">
                                            <?php echo ucfirst($req['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($req['status'] == 'pending'): ?>
                                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveReqModal<?php echo $req['id']; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectReqModal<?php echo $req['id']; ?>">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            
                                            <!-- Approve Modal -->
                                            <div class="modal fade" id="approveReqModal<?php echo $req['id']; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title">Approve Blood Request</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                                <input type="hidden" name="user_id" value="<?php echo $req['user_id']; ?>">
                                                                <input type="hidden" name="blood_group" value="<?php echo $req['blood_group']; ?>">
                                                                <input type="hidden" name="units_needed" value="<?php echo $req['units_needed']; ?>">
                                                                <input type="hidden" name="action" value="approve">
                                                                
                                                                <p><strong>Patient:</strong> <?php echo htmlspecialchars($req['patient_name']); ?></p>
                                                                <p><strong>Blood Group:</strong> <?php echo $req['blood_group']; ?></p>
                                                                <p><strong>Units Needed:</strong> <?php echo $req['units_needed']; ?></p>
                                                                <p><strong>Reason:</strong> <?php echo htmlspecialchars($req['reason']); ?></p>
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Admin Notes</label>
                                                                    <textarea class="form-control" name="admin_notes" rows="2"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-success">Approve Request</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectReqModal<?php echo $req['id']; ?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Reject Blood Request</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                                <input type="hidden" name="user_id" value="<?php echo $req['user_id']; ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                                
                                                                <div class="mb-3">
                                                                    <label class="form-label">Reason for Rejection *</label>
                                                                    <textarea class="form-control" name="admin_notes" rows="3" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Reject Request</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php elseif ($req['status'] == 'approved'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                                <input type="hidden" name="user_id" value="<?php echo $req['user_id']; ?>">
                                                <input type="hidden" name="action" value="fulfill">
                                                <button type="submit" class="btn btn-sm btn-info" title="Mark as Fulfilled">
                                                    <i class="fas fa-check-double"></i> Fulfill
                                                </button>
                                            </form>
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
