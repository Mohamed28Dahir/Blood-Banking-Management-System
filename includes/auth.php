<?php
/**
 * Blood Donation Management System (BDMS)
 * Authentication Middleware
 * 
 * This file contains functions to check authentication and authorization
 */

// Prevent direct access
if (!defined('BDMS_ROOT')) {
    die('Direct access not permitted');
}

/**
 * Require user to be logged in
 * Redirects to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Please login to access this page';
        redirect('login.php');
    }
}

/**
 * Require user to be admin
 * Redirects to user dashboard if not admin
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        $_SESSION['error'] = 'Access denied. Admin privileges required';
        redirect('user/dashboard.php');
    }
}

/**
 * Require user to be regular user (not admin)
 * Redirects to admin dashboard if admin
 */
function requireUser() {
    requireLogin();
    
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    }
}

/**
 * Require user account to be approved
 * Redirects with message if account is pending or blocked
 */
function requireApproved() {
    requireLogin();
    
    if (isset($_SESSION['status']) && $_SESSION['status'] !== 'approved') {
        $status = $_SESSION['status'];
        
        if ($status === 'pending') {
            $_SESSION['error'] = 'Your account is pending admin approval. Please wait for approval to access the system.';
        } elseif ($status === 'blocked') {
            $_SESSION['error'] = 'Your account has been blocked. Please contact the administrator.';
        }
        
        // Logout user
        session_destroy();
        redirect('login.php');
    }
}

/**
 * Check if user is guest (not logged in)
 * Redirects to appropriate dashboard if already logged in
 */
function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        if (isAdmin()) {
            redirect('admin/dashboard.php');
        } else {
            redirect('user/dashboard.php');
        }
    }
}

/**
 * Login user and create session
 * 
 * @param array $user User data from database
 */
function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['status'] = $user['status'];
    
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
}

/**
 * Logout user and destroy session
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

?>
