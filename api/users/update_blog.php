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

$blog_id = isset($_POST["blog_id"]) ? clean_input($_POST["blog_id"] ) : null;
$title = isset($_POST["title"]) ? clean_input($_POST["title"] ) : null;
$content = isset($_POST["content"]) ? clean_input($_POST["content"] ) : null;
$image = isset($_POST["image"]) ? clean_input($_POST["image"]) : null;

// Optional comment update inputs
$comment_id = isset($_POST["comment_id"])? clean_input($_POST["comment_id"] ): null;
$comment_text = isset ($_POST["comment_text"])? clean_input($_POST["comment_text"] ): null;

// Check required blog fields
if (!$blog_id || !$title || !$content) {
   response(false, "blog_id, title and content are required.");
}

// First, verify the blog belongs to this user
$check_blog = $conn->prepare("SELECT blog_id FROM blogs WHERE blog_id = ? AND author_id = ?");
$check_blog->bind_param("ii", $blog_id, $author_id);
$check_blog->execute();
$check_blog->store_result();

if ($check_blog->num_rows === 0) {
    response(false, "This blog does not belong to you or doesn't exist.");
}
$check_blog->close();

// Handle empty image
$image = $image === "" ? null : $image;

// Update blog
$update_blog = $conn->prepare("UPDATE blogs SET title = ?, content = ?, image = ? WHERE blog_id = ? AND author_id = ?");
$update_blog->bind_param("sssii", $title, $content, $image, $blog_id, $author_id);
$blog_updated = $update_blog->execute();
$update_blog->close();

// Optional comment update (only if comment_id and comment_text provided)
$comment_updated = false;
if ($comment_id && $comment_text) {
    $update_comment = $conn->prepare("UPDATE comments SET comment_text = ? WHERE comment_id = ? AND blog_id = ? AND author_id = ?");
    $update_comment->bind_param("siii", $comment_text, $comment_id, $blog_id, $author_id);
    $comment_updated = $update_comment->execute();
    $update_comment->close();
}

// Final response
if ($blog_updated) {
    response(true,  "Blog updated" . ($comment_updated ? " and your comment updated." : " successfully."));
} else {
 response(false,  "Failed to update blog.");
}
?>
