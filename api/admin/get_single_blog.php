<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';  // Use null coalescing to avoid error
$token = str_replace('Bearer ', '', $authHeader);

if (empty($authHeader)) {
    response(false, "Authorization token not found.");
}

$decoded = decode_jwt($token);

if (!$decoded || $decoded['role'] !== 'admin') {
    response(false, "Unauthorized: Access denied, Admins only.");
}

$admin_id = $decoded['admin_id'];

$verify_stmt = $conn->prepare("SELECT admin_id FROM admins WHERE admin_id = ?");
$verify_stmt->bind_param("i", $admin_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
  response(false, 'Unauthorized. Invalid token.');
}

// 2. GET BLOG ID FROM QUERY PARAMETER

$blog_id = isset($_GET['blog_id']) ? (int) clean_input($_GET["blog_id"]) : 0;

if ($blog_id <= 0) {
    response(false, "Invalid blog ID.");
}

// 3. FETCH SINGLE BLOG
$blog_stmt = $conn->prepare("SELECT blog_id, title, content, image, author_id FROM blogs WHERE blog_id = ?");
$blog_stmt->bind_param("i", $blog_id);
$blog_stmt->execute();
$blog_result = $blog_stmt->get_result();

if ($blog_result->num_rows === 0) {
   response(false, "Blog not found.");
}

$blog = $blog_result->fetch_assoc();

// 4. FETCH COMMENTS FOR THIS BLOG
$comment_stmt = $conn->prepare("SELECT comment_id, user_id, comment_text, created_at FROM comments WHERE blog_id = ? ORDER BY created_at DESC");
$comment_stmt->bind_param("i", $blog_id);
$comment_stmt->execute();
$comment_result = $comment_stmt->get_result();

$comments = [];
while ($comment = $comment_result->fetch_assoc()) {
    $comments[] = $comment;
}

// If there are no comments, don't include the empty "comments" field
if (empty($comments)) {
    unset($blog['comments']);
} else {
    $blog['comments'] = $comments;
}

// 5. RETURN FULL BLOG + COMMENTS
response(true, "Blog with comments fetched successfully.", ["data" => $blog]);

$blog_stmt->close();
$comment_stmt->close();
$conn->close();
?>
