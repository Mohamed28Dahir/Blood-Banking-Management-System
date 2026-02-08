<?php
/**
 * Blood Donation Management System
 * Download Medical Proof File - Admin Only
 */

define('BDMS_ROOT', dirname(__DIR__));
require_once BDMS_ROOT . '/includes/config.php';
require_once BDMS_ROOT . '/includes/functions.php';
require_once BDMS_ROOT . '/includes/auth.php';

requireAdmin();

if (!isset($_GET['file'])) {
    die('No file specified');
}

$filename = basename($_GET['file']);
$filepath = UPLOAD_PATH . $filename;

// Check if file exists
if (!file_exists($filepath)) {
    die('File not found');
}

// Get file info
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $filepath);
finfo_close($finfo);

// Set headers for file download/view
header('Content-Type: ' . $mime_type);
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));

// Output file
readfile($filepath);
exit();
?>
