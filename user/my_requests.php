<?php
/**
 * Blood Donation Management System
 * My Blood Requests
 */

$page_title = "My Blood Requests";
include '../includes/header.php';

requireUser();
requireApproved();

$user_id = $_SESSION['user_id'];

// Fetch all blood requests
$query = "SELECT * FROM blood_requests WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$requests = $stmt->get_result();
$stmt->close();
?>

<div class="container page-container">
    <div class="card">
        <div class="card-header">
            <h3 class="mb-0">
                <i class="fas fa-notes-medical"></i> My Blood Requests
            </h3>
        </div>
        <div class="card-body">
            <?php if ($requests->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Patient Name</th>
                                <th>Blood Group</th>
                                <th>Units</th>
                                <th>Hospital</th>
                                <th>Urgency</th>
                                <th>Required Date</th>
                                <th>Status</th>
                                <th>Requested On</th>
                                <th>Admin Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $count = 1;
                            while($req = $requests->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?php echo $count++; ?></td>
                                    <td><?php echo htmlspecialchars($req['patient_name']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getBloodGroupBadge($req['blood_group']); ?>">
                                            <?php echo htmlspecialchars($req['blood_group']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $req['units_needed']; ?></td>
                                    <td><?php echo htmlspecialchars($req['hospital_name']); ?></td>
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
                                    <td><?php echo formatDate($req['created_at']); ?></td>
                                    <td>
                                        <?php if ($req['admin_notes']): ?>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($req['admin_notes']); ?>
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
                    <i class="fas fa-notes-medical fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Blood Requests Yet</h4>
                    <p>You haven't submitted any blood requests yet.</p>
                    <a href="request_blood.php" class="btn btn-danger mt-3">
                        <i class="fas fa-plus"></i> Request Blood
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
