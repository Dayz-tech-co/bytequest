<?php
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

// Validate the blog_id passed via GET
if (!isset($_GET['blog_id']) || !is_numeric($_GET['blog_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid or missing blog_id."
    ]);
    exit;
}

$blog_id = (int) $_GET['blog_id'];

// 1. Fetch the blog
$blog_stmt = $conn->prepare("SELECT blog_id, title, content, image, author_id FROM blogs WHERE blog_id = ?");
$blog_stmt->bind_param("i", $blog_id);
$blog_stmt->execute();
$blog_result = $blog_stmt->get_result();

if ($blog_result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Blog not found."
    ]);
    exit;
}

$blog = $blog_result->fetch_assoc();

// 2. Fetch the comments for this blog
$comment_stmt = $conn->prepare("SELECT comment_id, user_id, comment FROM comments WHERE blog_id = ? ORDER BY comment_id DESC");
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
echo json_encode([
    "status" => "success",
    "message" => "Blog and its comments fetched successfully.",
    "data" => $blog
]);
?>
