<?php  
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

// 1. Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
   response(false, "Invalid Response Method.");
}

// Handle actions based on the `action` parameter
$action = clean_input($_POST["action"] ?? null);  // The action (e.g., 'update_blog', 'update_comment', 'update_user')

$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');

if (empty($token)){
   response(false, "Authorization Token Missing");
}

// 2. Decode the JWT
$decoded = decode_jwt($token);


if (!$decoded || isset($decoded['error'])) {
   response(false, "Invalid or expired token");
}

// 3. Check if it's really an admin
if (!isset($decoded['admin_id']) || $decoded['role'] !== 'admin') {
    response(false, "Unauthorized: Admin access required");
}

$admin_id = $decoded['admin_id'];

// Validate if admin ID actually exists in the database
$verify_admin = $conn->prepare("SELECT admin_id FROM admins WHERE admin_id = ?");
$verify_admin->bind_param("i", $admin_id);
$verify_admin->execute();
$admin_result = $verify_admin->get_result();

if ($admin_result->num_rows === 0) {
  response(false, "Access denied. Invalid admin credentials.");
}

// Depending on the action, proceed with the corresponding functionality
switch ($action) {
    case 'update_blog':
        // Blog status update
        $blog_id = clean_input($_POST["blog_id"] ?? null);
        $status = clean_input($_POST["status"] ?? null);

        if (!$blog_id || !$status) {
           response(false, "Blog ID and status are required.");
        }

        $valid_statuses = ['draft', 'published', 'archived'];
        if (!in_array($status, $valid_statuses)) {
           response(false,  "Invalid status.");
        }

        // Check if the blog exists
        $stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
           response(false, "Blog not found.");
        }

        // Update blog status
        $update = $conn->prepare("UPDATE blogs SET status = ? WHERE blog_id = ?");
        $update->bind_param("si", $status, $blog_id);

        if ($update->execute()) {
           response(true, "Blog status updated successfully.");
        } 
        else {
         response(false, "Failed to update blog status.");
        }
        break;

    case 'update_comment':
        // Comment status update
        $comment_id = clean_input($_POST["comment_id"] ?? null);
        $status = clean_input($_POST["status"] ?? null);

        if (!$comment_id || !$status) {
            response(false, "Comment ID and status are required.");
        }

        $valid_statuses = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $valid_statuses)) {
            response(false, "Invalid status.");
        }

        // Check if the comment exists
        $stmt = $conn->prepare("SELECT * FROM comments WHERE comment_id = ?");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            response(false, "Comment not found.");
        }

        // Update comment status
        $update = $conn->prepare("UPDATE comments SET status = ? WHERE comment_id = ?");
        $update->bind_param("si", $status, $comment_id);

        if ($update->execute()) {
            response(true, "Comment status updated successfully.");
        } else {
           response(false, "Failed to update comment.");
        }
        break;

    case 'update_user':
        // User status and role update
        $status = clean_input($_POST["status"] ?? null);  // user account status (active, suspended, banned)
        $role = clean_input($_POST["role"] ?? null);      // user role (user or admin)
        $id = clean_input($_POST["id"] ?? null);          // user ID

        // Validate inputs
        if (!$status || !$role || !$id) {
           response(false, "All fields (status, role, and id) are required.");
        }

        if (!in_array($status, ['active', 'suspended', 'banned'])) {
           response(false, "Invalid status.");
        }

        if (!in_array($role, ['user', 'admin'])) {
          response(false, "Invalid role.");
        }

        // Validate admin
        $admin_stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
        $admin_stmt->bind_param("i", $admin_id);
        $admin_stmt->execute();
        $admin_result = $admin_stmt->get_result();

        if ($admin_result->num_rows === 0) {
           response(false, "Unauthorized: admin not found.");
        }

        $admin_data = $admin_result->fetch_assoc();
        if ($admin_data["role"] !== "admin") {
           response(true,  "Only admins can update user status.");
        }

        // Validate user
        $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows === 0) {
           response(false, "User not found.");
        }

        // Update user status and role
        $update_stmt = $conn->prepare("UPDATE users SET status = ?, role = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $status, $role, $id);

        if ($update_stmt->execute()) {
            response(true, "User status and role updated successfully.");
        } else {
           response(false, "Failed to update user.");
        }
        break;

    default:
        response(false, "Invalid action.");
        break;
}
?>
