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
$author_id = $decoded['id'];

// Get the data from the POST request
$blog_id = clean_input($_POST["blog_id"] ?? null);
$comment = clean_input($_POST["comment"] ?? null);

// Check required fields
if (!$blog_id || !$comment) {
   response(false, "blog_id and comment are required.");
}

// Step 1: Check if the blog exists
$check_stmt = $conn->prepare("SELECT id FROM blogs WHERE id = ?");
if (!$check_stmt) {
    response(false, "Failed to prepare blog check statement.");
}
$check_stmt->bind_param("i", $blog_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows === 0) {
    response(false, "Blog not found. Cannot add comment.");
}
$check_stmt->close();

// Step 2: Insert the comment
$stmt = $conn->prepare("INSERT INTO comments (blog_id, author_id, comment) VALUES (?, ?, ?)");
if ($stmt === false) {
    response(false, "Failed to prepare SQL statement.");
}

if (!$stmt->bind_param("iis", $blog_id, $author_id, $comment)) {
    response(false, "Failed to bind parameters.");
}

if ($stmt->execute()) {
    response(true, "Comment created successfully.");
} else {
    response(false, "Failed to create comment.");
}

$stmt->close();
?>
