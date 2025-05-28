<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php?error=no_id");
    exit();
}

$applicationId = $_GET['id'];
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : '';

require_once '../config/database.php';

// Get application details for confirmation
$stmt = $pdo->prepare("SELECT a.*, u.id as user_id FROM applicants a 
                      LEFT JOIN users u ON a.user_id = u.id 
                      WHERE a.id = ?");
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

if (!$application) {
    header("Location: dashboard.php?error=not_found");
    exit();
}

// Process deletion if confirmed
if ($confirm === 'yes') {
    try {
        // Start transaction
        $pdo->beginTransaction();

        // Delete application
        $deleteStmt = $pdo->prepare("DELETE FROM applicants WHERE id = ?");
        $deleteStmt->execute([$applicationId]);

        // Log the deletion
        $logFile = __DIR__ . '/../logs/admin_actions.log';
        $logDir = dirname($logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logMessage = date('Y-m-d H:i:s') . " - Admin ID: {$_SESSION['admin_id']} menghapus pendaftaran ID: {$applicationId}, Nomor: {$application['registration_number']}\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);

        // Commit transaction
        $pdo->commit();

        // Redirect with success message
        header("Location: dashboard.php?deleted=1");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();

        // Redirect with error message
        header("Location: dashboard.php?error=delete_failed");
        exit();
    }
} else {
    // Redirect back to dashboard if not confirmed
    header("Location: dashboard.php");
    exit();
}
