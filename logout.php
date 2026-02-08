<?php
/**
 * Blood Donation Management System
 * Logout Handler
 */

// Define root constant
define('BDMS_ROOT', __DIR__);

// Include configuration and authentication
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Logout user
logoutUser();

// Redirect to homepage
redirect('index.php', 'You have been logged out successfully');
?>
