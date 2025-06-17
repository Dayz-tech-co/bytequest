<?php
require_once "../config/bootstrap.php";
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// Get Authorization header
$headers = apache_request_headers();

$authHeader = $headers['Authorization'];
$token = str_replace("Bearer ", "", $authHeader);

if (empty ($token)){
    response(false, "Authorization Token Missing");
}

$decoded = decode_jwt($token);
if (!$decoded || !isset($decoded['id'])) {
    response(false, "Invalid or expired token.");
}

// 🔐 Get user ID from token
$user_id = $decoded['id'];

// Get blog_id from the request
$blog_id = isset($_POST["blog_id"]) ? clean_input($_POST['blog_id']) : 0;
$comment_id = isset($_POST["comment_id"]) ? clean_input($_POST["comment_id"]) : 0;
if (!$blog_id) {
   response(false, "blog_id is required.");
}

try {
    // Delete comments related to the blog
    $deleteComments = $conn->prepare("DELETE FROM comments WHERE blog_id = ? AND author_id =? AND comment_id");
   $deleteComments->bind_param("iii", $blog_id, $author_id, $comment_id);
   $deleteComments->execute();
   if ($deleteComments->execute()){
    response(true, "Comments Deleted Successfully.");

   } else {
    response(false, "Failed to delete comments");
   }
    $deleteComments->close();

    // Check if this blog belongs to the user
$checkStmt = $conn->prepare("SELECT author_id FROM blogs WHERE blog_id = ?, AND author_id = ?");
$checkStmt->bind_param("ii", $blog_id, $author_id);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows===0){
    response(false, "This Blog Doesn't Belongs To You Or Exist.");
}

// Begin a transaction to ensure both deletions happen together
$conn->begin_transaction();

   // Delete the blog itself
    $delete_blog_stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
    $delete_blog_stmt->bind_param("i", $blog_id);
    $delete_blog_stmt->execute();

    // Commit the transaction
    $conn->commit();

  response(true, "Blog and associated comments deleted successfully.");
} catch (Exception $e) {
    $conn->rollback();
    response(false, "Error occurred while deleting.", [
        "error" => $e->getMessage()
    ]);
}
?>
