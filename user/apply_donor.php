<?php
/**
 * Blood Donation Management System
 * Donor Application Form
 */

$page_title = "Apply as Donor";
include '../includes/header.php';

requireUser();
requireApproved();

$errors = [];
$success = '';

// Check if user already has an approved donor application
$check_query = "SELECT id FROM donors WHERE user_id = ? AND status = 'approved'";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->store_result();
$already_donor = $stmt->num_rows > 0;
$stmt->close();

// Process donor application
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $blood_group = sanitize($_POST['blood_group']);
    $age = (int)$_POST['age'];
    $weight = (float)$_POST['weight'];
    $last_donation_date = !empty($_POST['last_donation_date']) ? sanitize($_POST['last_donation_date']) : null;
    $medical_conditions = sanitize($_POST['medical_conditions']);
    
    // Validate inputs
    if (empty($blood_group)) {
        $errors[] = "Please select your blood group";
    }
    
    if ($age < 18 || $age > 65) {
        $errors[] = "Age must be between 18 and 65 years";
    }
    
    if ($weight < 50) {
        $errors[] = "Weight must be at least 50 kg to donate blood";
    }
    
    // Validate file upload
    if (!isset($_FILES['medical_proof']) || $_FILES['medical_proof']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Medical proof document is required";
    } else {
        $upload_result = validateUpload($_FILES['medical_proof']);
        if (!$upload_result['success']) {
            $errors[] = $upload_result['message'];
        }
    }
    
    // If no errors, process the application
    if (empty($errors)) {
        $upload_result = uploadFile($_FILES['medical_proof']);
        
        if ($upload_result['success']) {
            $filename = $upload_result['filename'];
            
            $stmt = $conn->prepare("INSERT INTO donors (user_id, blood_group, age, weight, last_donation_date, medical_conditions, medical_proof, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("isiisss", $_SESSION['user_id'], $blood_group, $age, $weight, $last_donation_date, $medical_conditions, $filename);
            
            if ($stmt->execute()) {
                // Create notification for user
                createNotification($_SESSION['user_id'], "Your donor application has been submitted and is pending admin review.", "info");
                
                $_SESSION['success'] = "Donor application submitted successfully! Admin will review and approve your application.";
                redirect('dashboard.php');
            } else {
                $errors[] = "Failed to submit application. Please try again.";
            }
            $stmt->close();
        } else {
            $errors[] = $upload_result['message'];
        }
    }
}
?>

<div class="container page-container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">
                        <i class="fas fa-hand-holding-heart"></i> Apply as Blood Donor
                    </h3>
                </div>
                <div class="card-body">
                    <?php if ($already_donor): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-check-circle"></i>
                            You are already an approved donor! Thank you for your contribution.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data" id="donorForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="blood_group" class="form-label">
                                    <i class="fas fa-tint"></i> Blood Group *
                                </label>
                                <select class="form-select" id="blood_group" name="blood_group" required>
                                    <option value="">Select Blood Group</option>
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
                                <label for="age" class="form-label">
                                    <i class="fas fa-calendar"></i> Age (years) *
                                </label>
                                <input type="number" class="form-control" id="age" name="age" min="18" max="65" required>
                                <small class="text-muted">18-65 years</small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="weight" class="form-label">
                                    <i class="fas fa-weight"></i> Weight (kg) *
                                </label>
                                <input type="number" class="form-control" id="weight" name="weight" min="50" step="0.1" required>
                                <small class="text-muted">Min 50 kg</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="last_donation_date" class="form-label">
                                <i class="fas fa-calendar-alt"></i> Last Donation Date (if applicable)
                            </label>
                            <input type="date" class="form-control" id="last_donation_date" name="last_donation_date" max="<?php echo date('Y-m-d'); ?>">
                            <small class="text-muted">Leave blank if this is your first donation</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="medical_conditions" class="form-label">
                                <i class="fas fa-notes-medical"></i> Medical Conditions (if any)
                            </label>
                            <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="3" placeholder="List any medical conditions or medications you are currently taking"></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label for="medical_proof" class="form-label">
                                <i class="fas fa-file-upload"></i> Upload Medical Proof *
                            </label>
                            <input type="file" class="form-control" id="medical_proof" name="medical_proof" accept=".pdf,.jpg,.jpeg,.png" required onchange="previewFile(this)">
                            <small class="text-muted d-block mt-1">
                                Upload medical certificate or health report (PDF or Image, max 5MB)
                            </small>
                            <div id="fileInfo"></div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle"></i> Important Information:</h6>
                            <ul class="mb-0 small">
                                <li>You must be between 18-65 years old</li>
                                <li>Minimum weight requirement is 50 kg</li>
                                <li>Wait at least 3 months between blood donations</li>
                                <li>Must be in good health and not taking certain medications</li>
                                <li>Medical proof will be verified by admin</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i> Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    validateForm('donorForm');
</script>

<?php include '../includes/footer.php'; ?>
