<?php
require_once "../config/bootstrap.php";

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

// Validate the blog_id passed via GET
if (!isset($_GET['blog_id']) || !is_numeric($_GET['blog_id'])) {
 response(false,  "Invalid or missing blog_id.");
}

$blog_id = clean_input($_GET["blog_id"]) ?? null;

// 1. Fetch the blog
$blog_stmt = $conn->prepare("SELECT blog_id, title, content, image, author_id FROM blogs WHERE blog_id = ?");
if (!$blog_stmt) {
   response(false, "Failed to prepare the query for fetching blog.");
}
$blog_stmt->bind_param("i", $blog_id);
$blog_stmt->execute();
$blog_result = $blog_stmt->get_result();

if ($blog_result->num_rows === 0) {
    response(false,  "Blog not found.");
}

$blog = $blog_result->fetch_assoc();

// 2. Fetch the comments for this blog
$comment_stmt = $conn->prepare("SELECT comment_id, user_id, comment FROM comments WHERE blog_id = ? ORDER BY comment_id DESC");
if (!$comment_stmt) {
   response(false, "Failed to prepare the query for fetching comments.");
}
$comment_stmt->bind_param("i", $blog_id);
$comment_stmt->execute();
$comments_result = $comment_stmt->get_result();

$comments = [];
while ($comment = $comments_result->fetch_assoc()) {
    $comments[] = $comment;
}

// 3. Attach comments to blog
$blog['comments'] = $comments;

// 4. Return blog with comments
response(true, "Blog and its comments fetched successfully.", ["data" => $blog]);

?>
