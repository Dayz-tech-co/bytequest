<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$decoded = decode_jwt($token);

if (empty ($token)){
       response(false, "Authorization token not Found");
}

if (!$decoded || ($decoded['role'] ?? '') !== 'admin') {
     response(false, "Unauthorized access, Admin only granted.");
}

$admin_id = $decoded['admin_id'];
$comment_id = isset($_POST["comment_id"])? clean_input($_POST['comment_id']): null;

// Check for required fields
if (!$admin_id || !$comment_id) {
   response(false, "Missing required parameters.");
}

// Confirm if admin exists
$admin_stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();

if ($admin_result->num_rows === 0) {
      response(false, "unauthorized: Admin not found");
}

// Confirm if the comment exists
$comment_stmt = $conn->prepare("SELECT * FROM comments WHERE comment_id = ?");
$comment_stmt->bind_param("i", $comment_id);
$comment_stmt->execute();
$comment_result = $comment_stmt->get_result();

if ($comment_result->num_rows === 0) {
       response(false, "Comment Not Found.");
}

// Delete the comment
$delete_stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
$delete_stmt->bind_param("i", $comment_id);

if ($delete_stmt->execute()) {
       response(true, "Comment deleted Successfully.");
} else {
       response(false, "Failed to delete Comment.");
}
?>
