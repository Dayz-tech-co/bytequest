<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$decoded = decode_jwt($token);

if (empty($token)){
  response(false, "Authorization Token Missing");
}

if (!$decoded || ($decoded['role'] ?? '') !== 'admin') {
  response(false, "Unauthorized access, Admin only granted.");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    response(false, "Invalid request method.");
}

$admin_id = $decoded['admin_id'];
$blog_id = isset ($_POST["blog_id"]) ? clean_input($_POST["blog_id"]) : null;

if (!$blog_id || !$admin_id) {
    response(false, "Both fields (Blog id and Admin id ) are Required.");
}

// Confirm if the blog exists
$blog_stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
$blog_stmt->bind_param("i", $blog_id);
$blog_stmt->execute();
$blog_result = $blog_stmt->get_result();

if ($blog_result->num_rows === 0) {
   response(false, "Blog not found, Unable to delete.");
}

// Confirm if admin exists
$admin_stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();

if ($admin_result->num_rows === 0) {
   response(false, "unauthorized: Admin not found");
}

// Start the transaction to ensure atomicity
$conn->begin_transaction();

try {
    // Delete all comments associated with the blog
    $delete_comments_stmt = $conn->prepare("DELETE FROM comments WHERE blog_id = ?");
    $delete_comments_stmt->bind_param("i", $blog_id);
    $delete_comments_stmt->execute();

    // Delete the blog itself
    $delete_blog_stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
    $delete_blog_stmt->bind_param("i", $blog_id);
    $delete_blog_stmt->execute();

    // Commit the transaction
    $conn->commit();

  response(true, "Blog and associated comments deleted successfully.");
} catch (Exception $e) {
    // Rollback the transaction if something goes wrong
    $conn->rollback();

   response(false, "Failed to delete blog and associated comments.",
    [ "error" => $e->getMessage()]);
      
}
?>
