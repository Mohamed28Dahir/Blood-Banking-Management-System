<?php
/**
 * Blood Donation Management System
 * User Dashboard
 */

$page_title = "My Dashboard";
include '../includes/header.php';

// Require user to be logged in and approved
requireUser();
requireApproved();

$user_id = $_SESSION['user_id'];

// Fetch user statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM donors WHERE user_id = ? AND status = 'approved') as approved_donations,
        (SELECT COUNT(*) FROM donors WHERE user_id = ? AND status = 'pending') as pending_donations,
        (SELECT COUNT(*) FROM blood_requests WHERE user_id = ? AND status = 'approved') as approved_requests,
        (SELECT COUNT(*) FROM blood_requests WHERE user_id = ? AND status = 'pending') as pending_requests
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch recent notifications
$notif_query = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($notif_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();

// Fetch recent donor applications
$donor_query = "SELECT * FROM donors WHERE user_id = ? ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($donor_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$donors = $stmt->get_result();
$stmt->close();

// Fetch recent blood requests
$request_query = "SELECT * FROM blood_requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 3";
$stmt = $conn->prepare($request_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests = $stmt->get_result();
$stmt->close();
?>

<div class="container page-container">
    <!-- Welcome Header -->
    <div class="mb-4">
        <h1 class="text-gradient">
            <i class="fas fa-tachometer-alt"></i> Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!
        </h1>
        <p class="text-muted">Manage your blood donation activities and requests</p>
    </div>

    <?php echo displayMessage(); ?>
    <?php echo displaySuccess(); ?>
    <?php echo displayError(); ?>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="stat-card success position-relative">
                <h3><?php echo $stats['approved_donations']; ?></h3>
                <p>Approved Donations</p>
                <i class="fas fa-check-circle stat-icon text-success"></i>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card warning position-relative">
                <h3><?php echo $stats['pending_donations']; ?></h3>
                <p>Pending Donations</p>
                <i class="fas fa-clock stat-icon text-warning"></i>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card info position-relative">
                <h3><?php echo $stats['approved_requests']; ?></h3>
                <p>Approved Requests</p>
                <i class="fas fa-heartbeat stat-icon text-info"></i>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="stat-card primary position-relative">
                <h3><?php echo $stats['pending_requests']; ?></h3>
                <p>Pending Requests</p>
                <i class="fas fa-hourglass-half stat-icon text-primary"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-bolt"></i> Quick Actions
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <a href="apply_donor.php" class="btn btn-success w-100">
                        <i class="fas fa-hand-holding-heart me-2"></i> Apply as Donor
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="request_blood.php" class="btn btn-danger w-100">
                        <i class="fas fa-notes-medical me-2"></i> Request Blood
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="profile.php" class="btn btn-primary w-100">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Notifications -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-bell"></i> Recent Notifications
                </div>
                <div class="card-body">
                    <?php if ($notifications->num_rows > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php while($notif = $notifications->fetch_assoc()): ?>
                                <div class="list-group-item <?php echo $notif['is_read'] ? '' : 'bg-light'; ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <span class="badge <?php echo getStatusBadge($notif['type']); ?>">
                                            <?php echo ucfirst($notif['type']); ?>
                                        </span>
                                        <small class="text-muted"><?php echo formatDateTime($notif['created_at']); ?></small>
                                    </div>
                                    <p class="mb-0 mt-2"><?php echo htmlspecialchars($notif['message']); ?></p>
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

        <!-- Recent Applications -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <i class="fas fa-hand-holding-heart"></i> My Donor Applications
                </div>
                <div class="card-body">
                    <?php if ($donors->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Blood Group</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($donor = $donors->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?php echo getBloodGroupBadge($donor['blood_group']); ?>">
                                                    <?php echo htmlspecialchars($donor['blood_group']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadge($donor['status']); ?>">
                                                    <?php echo ucfirst($donor['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($donor['created_at']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="my_applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-hand-holding-heart fa-3x mb-3 d-block"></i>
                            No donor applications yet
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Requests -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-notes-medical"></i> My Blood Requests
                </div>
                <div class="card-body">
                    <?php if ($requests->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient Name</th>
                                        <th>Blood Group</th>
                                        <th>Units</th>
                                        <th>Hospital</th>
                                        <th>Urgency</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($request = $requests->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                                            <td>
                                                <span class="badge <?php echo getBloodGroupBadge($request['blood_group']); ?>">
                                                    <?php echo htmlspecialchars($request['blood_group']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $request['units_needed']; ?></td>
                                            <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                            <td>
                                                <span class="badge <?php echo getUrgencyBadge($request['urgency']); ?>">
                                                    <?php echo ucfirst($request['urgency']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo getStatusBadge($request['status']); ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($request['created_at']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="my_requests.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-notes-medical fa-3x mb-3 d-block"></i>
                            No blood requests yet
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
