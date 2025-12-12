<?php
// mechanic/update_status.php
include "../auth_mechanic.php";
include "../connection.php";

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Use relative path - we're in mechanic/ directory
    header("Location: tasks.php");
    exit();
}

// Get mechanic ID from session
$mechanic_id = $_SESSION['user']['user_id'];

// Get POST data
$maintenance_id = isset($_POST['job_id']) ? intval($_POST['job_id']) : 0;
$new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

// Validate status
$allowed_statuses = ['pending', 'in_progress', 'done'];
if (!in_array($new_status, $allowed_statuses)) {
    $_SESSION['error'] = "Invalid status. Must be: pending, in_progress, or done";
    header("Location: tasks.php");
    exit();
}

// Validate maintenance_id
if ($maintenance_id <= 0) {
    $_SESSION['error'] = "Invalid job ID";
    header("Location: tasks.php");
    exit();
}

// Check if this job belongs to the mechanic
$check_stmt = $conn->prepare("
    SELECT 1 FROM works_on 
    WHERE maintenance_id = ? AND mechanic_id = ?
");
$check_stmt->bind_param("ii", $maintenance_id, $mechanic_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    $_SESSION['error'] = "Access denied. This job is not assigned to you.";
    $check_stmt->close();
    header("Location: tasks.php");
    exit();
}
$check_stmt->close();

// Update the status
$update_stmt = $conn->prepare("
    UPDATE maintenance_record 
    SET status = ? 
    WHERE maintenance_id = ?
");
$update_stmt->bind_param("si", $new_status, $maintenance_id);

if ($update_stmt->execute()) {
    if ($update_stmt->affected_rows > 0) {
        $_SESSION['success'] = "Status updated successfully to: " . ucfirst(str_replace('_', ' ', $new_status));
    } else {
        $_SESSION['info'] = "No changes made. Status was already: " . ucfirst(str_replace('_', ' ', $new_status));
    }
} else {
    $_SESSION['error'] = "Error updating status: " . $conn->error;
}

$update_stmt->close();
$conn->close();

// Redirect back to tasks page
header("Location: tasks.php");
exit();