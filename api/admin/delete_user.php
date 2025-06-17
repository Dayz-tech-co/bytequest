<?php
require_once "../config/bootstrap.php";

$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$decoded = decode_jwt($token);


if(empty($token)){
   response(false, "Authorization token not found.");
}

if (!$decoded || ($decoded['role'] ?? '') !== 'admin') {
      response(false, "Unauthorized access, Admin only granted.");
}

$admin_id = $decoded['admin_id'];

// Validate admin ID from database
$admin_check = $conn->prepare("SELECT admin_id FROM admins WHERE admin_id = ?");
$admin_check->bind_param("i", $admin_id);
$admin_check->execute();
$admin_result = $admin_check->get_result();

if ($admin_result->num_rows === 0) {
   response(false, " Invalid admin or token inserted");
}

// Validate user_id to delete
$user_id = isset($_POST['user_id']) ? clean_input((int)$_POST['user_id']) : null;

if (!$user_id) {
   response(false, "User id is required.");
}

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    response(false, "User not found.");
}

// Step 1: Delete user's comments
$delete_comments = $conn->prepare("DELETE FROM comments WHERE user_id = ?");
$delete_comments->bind_param("i", $user_id);
$delete_comments->execute();

// Step 2: Delete user's blogs
$delete_blogs = $conn->prepare("DELETE FROM blogs WHERE user_id = ?");
$delete_blogs->bind_param("i", $user_id);
$delete_blogs->execute();

// Step 3: Delete the user
$delete_user = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete_user->bind_param("i", $user_id);
$delete_user->execute();

response(true, "User, their blogs, and comments deleted successfully");
?>
