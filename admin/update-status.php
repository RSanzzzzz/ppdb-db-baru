<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if ID and status are provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$applicationId = $_GET['id'];
$newStatus = isset($_GET['status']) ? $_GET['status'] : (isset($_POST['status']) ? $_POST['status'] : '');
$adminNotes = isset($_POST['admin_notes']) ? $_POST['admin_notes'] : '';

// Validate status
$validStatuses = ['pending', 'verified', 'accepted', 'rejected'];
if (!in_array($newStatus, $validStatuses)) {
    header("Location: dashboard.php");
    exit();
}

require_once '../config/database.php';
require_once '../includes/emailHelper.php';

// Get current application data to check if status or notes have changed
$currentDataStmt = $pdo->prepare("SELECT a.*, u.email, u.name as user_name FROM applicants a 
                                 JOIN users u ON a.user_id = u.id 
                                 WHERE a.id = ?");
$currentDataStmt->execute([$applicationId]);
$currentData = $currentDataStmt->fetch();

if (!$currentData) {
    header("Location: dashboard.php?error=application_not_found");
    exit();
}

$statusChanged = $currentData['status'] !== $newStatus;
$notesChanged = $adminNotes !== $currentData['admin_notes'];

// Update status and admin notes in database
$updateStmt = $pdo->prepare("UPDATE applicants SET status = ?, admin_notes = ? WHERE id = ?");
$result = $updateStmt->execute([$newStatus, $adminNotes, $applicationId]);

// Send email notification if status or notes changed
if ($result && ($statusChanged || $notesChanged)) {
    // Get user email and name
    $userEmail = $currentData['email'];
    $userName = $currentData['full_name'] ?? $currentData['user_name'];
    $regNumber = $currentData['registration_number'];
    
    // Create email content
    $subject = "Update Status Pendaftaran PPDB - " . $regNumber;
    $emailContent = getStatusUpdateEmailTemplate($userName, $regNumber, $newStatus, $adminNotes);
    
    // Send email
    $emailSent = sendEmail($userEmail, $subject, $emailContent);
    
    // Set session variable to show notification about email status
    $_SESSION['email_status'] = $emailSent ? 'success' : 'failed';
    
    // Log email status
    $logFile = __DIR__ . '/../logs/email_status.log';
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logMessage = date('Y-m-d H:i:s') . " - Email ke $userEmail: " . 
                 ($emailSent ? "BERHASIL" : "GAGAL") . 
                 ", Status: $newStatus, ID: $applicationId\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Redirect back to dashboard or view page
if (isset($_GET['return']) && $_GET['return'] === 'view') {
    header("Location: view-application.php?id=$applicationId&status_updated=1");
} else {
    header("Location: dashboard.php?status_updated=1");
}
exit();
