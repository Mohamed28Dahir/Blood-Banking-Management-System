<?php
/**
 * Blood Donation Management System
 * User Registration
 */

$page_title = "Register";
include 'includes/header.php';

// Redirect if already logged in
redirectIfLoggedIn();

$errors = [];

// Process registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name) || strlen($full_name) < 3) {
        $errors[] = "Full name must be at least 3 characters long";
    }
    
    if (!validateEmail($email)) {
        $errors[] = "Invalid email address";
    }
    
    if (!validatePhone($phone)) {
        $errors[] = "Invalid phone number format";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Email address already registered";
        }
        $stmt->close();
    }
    
    // Insert user if no errors
    if (empty($errors)) {
        $hashed_password = hashPassword($password);
        
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, address, password, role, status) VALUES (?, ?, ?, ?, ?, 'user', 'pending')");
        $stmt->bind_param("sssss", $full_name, $email, $phone, $address, $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Your account is pending admin approval. You will be notified once approved.";
            redirect('login.php');
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-user-plus"></i>
            <h2>Create Account</h2>
            <p class="mb-0">Join our blood donation community</p>
        </div>
        <div class="auth-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm" novalidate>
                <div class="mb-3">
                    <label for="full_name" class="form-label">
                        <i class="fas fa-user"></i> Full Name *
                    </label>
                    <input type="text" class="form-control" id="full_name" name="full_name" 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                           required minlength="3">
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email Address *
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone"></i> Phone Number *
                    </label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                           placeholder="+252-61-1234567" required>
                    <small class="text-muted">Format: +252-XX-XXXXXXX</small>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Address *
                    </label>
                    <textarea class="form-control" id="address" name="address" rows="3" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password *
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    <small class="text-muted">Minimum 6 characters</small>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock"></i> Confirm Password *
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p>Already have an account? 
                    <a href="login.php" class="text-decoration-none fw-bold">
                        <i class="fas fa-sign-in-alt"></i> Login Here
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    validateForm('registerForm');
</script>

<?php include 'includes/footer.php'; ?>
