<?php
/**
 * Blood Donation Management System
 * CSV Export Handler
 */

define('BDMS_ROOT', dirname(__DIR__));
require_once BDMS_ROOT . '/includes/config.php';
require_once BDMS_ROOT . '/includes/functions.php';
require_once BDMS_ROOT . '/includes/auth.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_POST['report_type'])) {
    redirect('reports.php', 'Invalid request');
}

$report_type = sanitize($_POST['report_type']);
$from_date = !empty($_POST['from_date']) ? sanitize($_POST['from_date']) : null;
$to_date = !empty($_POST['to_date']) ? sanitize($_POST['to_date']) : date('Y-m-d');

// Build filename
$filename = $report_type . '_report_' . date('Y-m-d_His') . '.csv';

// Generate report based on type
switch ($report_type) {
    case 'users':
        $status = sanitize($_POST['status']);
        
        $query = "SELECT id, full_name, email, phone, address, status, created_at FROM users WHERE role = 'user'";
        
        if ($status != 'all') {
            $query .= " AND status = '$status'";
        }
        
        if ($from_date) {
            $query .= " AND DATE(created_at) >= '$from_date'";
        }
        
        $query .= " AND DATE(created_at) <= '$to_date' ORDER BY created_at DESC";
        
        $result = $conn->query($query);
        $headers = ['ID', 'Full Name', 'Email', 'Phone', 'Address', 'Status', 'Registered On'];
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                $row['id'],
                $row['full_name'],
                $row['email'],
                $row['phone'],
                $row['address'],
                ucfirst($row['status']),
                formatDateTime($row['created_at'])
            ];
        }
        
        generateCSV($data, $headers, $filename);
        break;
    
    case 'donors':
        $status = sanitize($_POST['status']);
        $blood_group = sanitize($_POST['blood_group']);
        
        $query = "SELECT d.*, u.full_name, u.email, u.phone FROM donors d JOIN users u ON d.user_id = u.id WHERE 1=1";
        
        if ($status != 'all') {
            $query .= " AND d.status = '$status'";
        }
        
        if ($blood_group != 'all') {
            $query .= " AND d.blood_group = '$blood_group'";
        }
        
        if ($from_date) {
            $query .= " AND DATE(d.created_at) >= '$from_date'";
        }
        
        $query .= " AND DATE(d.created_at) <= '$to_date' ORDER BY d.created_at DESC";
        
        $result = $conn->query($query);
        $headers = ['ID', 'Donor Name', 'Email', 'Phone', 'Blood Group', 'Age', 'Weight (kg)', 'Last Donation', 'Medical Conditions', 'Status', 'Admin Notes', 'Applied On'];
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                $row['id'],
                $row['full_name'],
                $row['email'],
                $row['phone'],
                $row['blood_group'],
                $row['age'],
                $row['weight'],
                $row['last_donation_date'] ?: 'N/A',
                $row['medical_conditions'] ?: 'None',
                ucfirst($row['status']),
                $row['admin_notes'] ?: '-',
                formatDateTime($row['created_at'])
            ];
        }
        
        generateCSV($data, $headers, $filename);
        break;
    
    case 'requests':
        $status = sanitize($_POST['status']);
        $urgency = sanitize($_POST['urgency']);
        
        $query = "SELECT br.*, u.full_name, u.email FROM blood_requests br JOIN users u ON br.user_id = u.id WHERE 1=1";
        
        if ($status != 'all') {
            $query .= " AND br.status = '$status'";
        }
        
        if ($urgency != 'all') {
            $query .= " AND br.urgency = '$urgency'";
        }
        
        if ($from_date) {
            $query .= " AND DATE(br.created_at) >= '$from_date'";
        }
        
        $query .= " AND DATE(br.created_at) <= '$to_date' ORDER BY br.created_at DESC";
        
        $result = $conn->query($query);
        $headers = ['ID', 'Requested By', 'Email', 'Patient Name', 'Blood Group', 'Units Needed', 'Hospital', 'Hospital Address', 'Contact Number', 'Urgency', 'Required Date', 'Reason', 'Status', 'Admin Notes', 'Requested On'];
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                $row['id'],
                $row['full_name'],
                $row['email'],
                $row['patient_name'],
                $row['blood_group'],
                $row['units_needed'],
                $row['hospital_name'],
                $row['hospital_address'],
                $row['contact_number'],
                ucfirst($row['urgency']),
                formatDate($row['required_date']),
                $row['reason'],
                ucfirst($row['status']),
                $row['admin_notes'] ?: '-',
                formatDateTime($row['created_at'])
            ];
        }
        
        generateCSV($data, $headers, $filename);
        break;
    
    case 'blood_stock':
        $query = "SELECT * FROM blood_stock ORDER BY blood_group";
        $result = $conn->query($query);
        $headers = ['Blood Group', 'Units Available', 'Stock Status', 'Last Updated'];
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $stock_status = 'Out of Stock';
            if ($row['units_available'] > 5) {
                $stock_status = 'Adequate';
            } elseif ($row['units_available'] > 0) {
                $stock_status = 'Low Stock';
            }
            
            $data[] = [
                $row['blood_group'],
                $row['units_available'],
                $stock_status,
                formatDateTime($row['last_updated'])
            ];
        }
        
        generateCSV($data, $headers, $filename);
        break;
    
    default:
        redirect('reports.php', 'Invalid report type');
}
?>
