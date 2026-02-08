<?php
/**
 * Blood Donation Management System
 * Login Page
 */

$page_title = "Login";
include 'includes/header.php';

// Redirect if already logged in
redirectIfLoggedIn();

$error = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        // Fetch user from database
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (verifyPassword($password, $user['password'])) {
                // Check if account is approved
                if ($user['status'] === 'pending') {
                    $error = "Your account is pending admin approval. Please wait for approval.";
                } elseif ($user['status'] === 'blocked') {
                    $error = "Your account has been blocked. Please contact the administrator.";
                } else {
                    // Login successful
                    loginUser($user);
                    
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        redirect('admin/dashboard.php', 'Welcome back, ' . $user['full_name']);
                    } else {
                        redirect('user/dashboard.php', 'Welcome back, ' . $user['full_name']);
                    }
                }
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
        $stmt->close();
    }
}
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-sign-in-alt"></i>
            <h2>Login</h2>
            <p class="mb-0">Access your account</p>
        </div>
        <div class="auth-body">
            <?php echo displaySuccess(); ?>
            <?php echo displayError(); ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required autofocus>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-4">
                <p>Don't have an account? 
                    <a href="register.php" class="text-decoration-none fw-bold">
                        <i class="fas fa-user-plus"></i> Register Here
                    </a>
                </p>
            </div>
            
            <div class="mt-4 p-3 bg-light rounded">
                <p class="mb-2 fw-bold"><i class="fas fa-info-circle"></i> Demo Credentials:</p>
                <small class="text-muted">
                    <strong>Admin:</strong> admin@bdms.com / Admin@123<br>
                    <strong>User:</strong> ahmed.hassan@email.com / password
                </small>
            </div>
        </div>
    </div>
</div>

<script>
    validateForm('loginForm');
</script>

<?php include 'includes/footer.php'; ?>
