<?php
/**
 * Blood Donation Management System
 * Blood Request Form
 */

$page_title = "Request Blood";
include '../includes/header.php';

requireUser();
requireApproved();

$errors = [];

// Fetch blood stock for information
$stock_query = "SELECT * FROM blood_stock ORDER BY blood_group";
$stock_result = $conn->query($stock_query);

// Process blood request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_name = sanitize($_POST['patient_name']);
    $blood_group = sanitize($_POST['blood_group']);
    $units_needed = (int)$_POST['units_needed'];
    $hospital_name = sanitize($_POST['hospital_name']);
    $hospital_address = sanitize($_POST['hospital_address']);
    $contact_number = sanitize($_POST['contact_number']);
    $urgency = sanitize($_POST['urgency']);
    $required_date = sanitize($_POST['required_date']);
    $reason = sanitize($_POST['reason']);
    
    // Validate inputs
    if (empty($patient_name)) {
        $errors[] = "Patient name is required";
    }
    
    if (empty($blood_group)) {
        $errors[] = "Please select blood group";
    }
    
    if ($units_needed < 1 || $units_needed > 10) {
        $errors[] = "Units needed must be between 1 and 10";
    }
    
    if (empty($hospital_name) || empty($hospital_address)) {
        $errors[] = "Hospital information is required";
    }
    
    if (!validatePhone($contact_number)) {
        $errors[] = "Invalid contact number";
    }
    
    if (empty($urgency)) {
        $errors[] = "Please select urgency level";
    }
    
    if (empty($required_date)) {
        $errors[] = "Required date is required";
    } else {
        $req_date = strtotime($required_date);
        $today = strtotime(date('Y-m-d'));
        if ($req_date < $today) {
            $errors[] = "Required date cannot be in the past";
        }
    }
    
    if (empty($reason)) {
        $errors[] = "Reason for blood request is required";
    }
    
    // If no errors, submit the request
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO blood_requests (user_id, patient_name, blood_group, units_needed, hospital_name, hospital_address, contact_number, urgency, required_date, reason, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("isssisssss", $_SESSION['user_id'], $patient_name, $blood_group, $units_needed, $hospital_name, $hospital_address, $contact_number, $urgency, $required_date, $reason);
        
        if ($stmt->execute()) {
            // Create notification
            createNotification($_SESSION['user_id'], "Your blood request for patient '$patient_name' has been submitted and is pending admin approval.", "info");
            
            $_SESSION['success'] = "Blood request submitted successfully! Admin will review your request.";
            redirect('dashboard.php');
        } else {
            $errors[] = "Failed to submit request. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="container page-container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-notes-medical"></i> Request Blood
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Blood Stock Information -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-info-circle"></i> Current Blood Availability:</h6>
                        <div class="row g-2">
                            <?php while($stock = $stock_result->fetch_assoc()): ?>
                                <div class="col-6 col-md-3">
                                    <strong><?php echo $stock['blood_group']; ?>:</strong> 
                                    <span class="badge <?php echo $stock['units_available'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $stock['units_available']; ?> units
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    
                    <form method="POST" action="" id="requestForm">
                        <h5 class="mb-3"><i class="fas fa-user-injured"></i> Patient Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_name" class="form-label">
                                    <i class="fas fa-hospital-user"></i> Patient Name *
                                </label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="blood_group" class="form-label">
                                    <i class="fas fa-tint"></i> Blood Group *
                                </label>
                                <select class="form-select" id="blood_group" name="blood_group" required>
                                    <option value="">Select</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="units_needed" class="form-label">
                                    <i class="fas fa-vial"></i> Units Needed *
                                </label>
                                <input type="number" class="form-control" id="units_needed" name="units_needed" min="1" max="10" required>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        <h5 class="mb-3"><i class="fas fa-hospital"></i> Hospital Information</h5>
                        
                        <div class="mb-3">
                            <label for="hospital_name" class="form-label">
                                <i class="fas fa-h-square"></i> Hospital Name *
                            </label>
                            <input type="text" class="form-control" id="hospital_name" name="hospital_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="hospital_address" class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Hospital Address *
                            </label>
                            <textarea class="form-control" id="hospital_address" name="hospital_address" rows="2" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_number" class="form-label">
                                    <i class="fas fa-phone"></i> Contact Number *
                                </label>
                                <input type="tel" class="form-control" id="contact_number" name="contact_number" placeholder="+252-61-1234567" required>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="urgency" class="form-label">
                                    <i class="fas fa-exclamation-triangle"></i> Urgency *
                                </label>
                                <select class="form-select" id="urgency" name="urgency" required>
                                    <option value="">Select</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="required_date" class="form-label">
                                    <i class="fas fa-calendar-alt"></i> Required Date *
                                </label>
                                <input type="date" class="form-control" id="required_date" name="required_date" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="reason" class="form-label">
                                <i class="fas fa-file-medical"></i> Reason for Request *
                            </label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" placeholder="Describe the medical reason for the blood request" required></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle"></i> Important Notes:</h6>
                            <ul class="mb-0 small">
                                <li>All blood requests are subject to admin approval</li>
                                <li>Approval depends on blood stock availability</li>
                                <li>Make sure to provide accurate contact information</li>
                                <li>Critical requests are prioritized</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    validateForm('requestForm');
</script>

<?php include '../includes/footer.php'; ?>
