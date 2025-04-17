<?php
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Validate request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Request Method."
    ]);
    exit;
}

// Get POST data
$admin_id = $_POST['admin_id'] ?? null;
$comment_id = $_POST['comment_id'] ?? null;

// Check for required fields
if (!$admin_id || !$comment_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters."
    ]);
    exit;
}

// Check if admin exists
$admin_stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();

if ($admin_result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized: admin not found."
    ]);
    exit;
}

// Delete comment
$delete_stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
$delete_stmt->bind_param("i", $comment_id);

if ($delete_stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Comment deleted successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete comment."
    ]);
}
?>
