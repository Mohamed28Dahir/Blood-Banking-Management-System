<?php
/**
 * Blood Donation Management System
 * My Donor Applications
 */

$page_title = "My Donor Applications";
include '../includes/header.php';

requireUser();
requireApproved();

$user_id = $_SESSION['user_id'];

// Fetch all donor applications
$query = "SELECT * FROM donors WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result();
$stmt->close();
?>

<div class="container page-container">
    <div class="card">
        <div class="card-header">
            <h3 class="mb-0">
                <i class="fas fa-hand-holding-heart"></i> My Donor Applications
            </h3>
        </div>
        <div class="card-body">
            <?php if ($applications->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Blood Group</th>
                                <th>Age</th>
                                <th>Weight (kg)</th>
                                <th>Last Donation</th>
                                <th>Status</th>
                                <th>Applied On</th>
                                <th>Admin Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while($app = $applications->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td>
                                        <span class="badge <?php echo getBloodGroupBadge($app['blood_group']); ?>">
                                            <?php echo htmlspecialchars($app['blood_group']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $app['age']; ?></td>
                                    <td><?php echo $app['weight']; ?></td>
                                    <td>
                                        <?php echo $app['last_donation_date'] ? formatDate($app['last_donation_date']) : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadge($app['status']); ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($app['created_at']); ?></td>
                                    <td>
                                        <?php if ($app['admin_notes']): ?>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($app['admin_notes']); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-hand-holding-heart fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Donor Applications Yet</h4>
                    <p>You haven't applied as a donor yet.</p>
                    <a href="apply_donor.php" class="btn btn-success mt-3">
                        <i class="fas fa-plus"></i> Apply as Donor
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
