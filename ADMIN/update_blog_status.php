<?php
require_once '../CONFIG/bootstrap.php';
require_once "../CONFIG/functions.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'false',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$blog_id = clean_input($_POST['blog_id'] ?? '');
$title = clean_input($_POST['title'] ?? '');
$content = clean_input($_POST['content'] ?? '');
$status = clean_input($_POST['status'] ?? '');

if (empty($blog_id) || empty($title) || empty($content)) {
    echo json_encode([
        'status' => 'false',
        'message' => 'blog_id, title, and content are required.'
    ]);
    exit;
}

// Check if the blog exists
$check_blog = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
$check_blog->bind_param("i", $blog_id);
$check_blog->execute();
$blog_result = $check_blog->get_result();

if ($blog_result->num_rows === 0) {
    echo json_encode([
        'status' => 'false',
        'message' => 'Blog not found.'
    ]);
    exit;
}

if ($status === 'deleted') {
    // Optional: check if any comments exist (can skip if you're deleting anyway)
    $check_comments = $conn->prepare("SELECT comment_id FROM comments WHERE blog_id = ?");
    $check_comments->bind_param("i", $blog_id);
    $check_comments->execute();
    $comments_result = $check_comments->get_result();

    // Delete comments if any exist
    if ($comments_result->num_rows > 0) {
        $delete_comments = $conn->prepare("DELETE FROM comments WHERE blog_id = ?");
        $delete_comments->bind_param('i', $blog_id);
        $delete_comments->execute();
    }

    // Delete the blog itself
    $delete_blog = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
    $delete_blog->bind_param('i', $blog_id);

    if ($delete_blog->execute()) {
        echo json_encode([
            'status' => 'true',
            'message' => 'Blog and its comments deleted successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'false',
            'message' => 'Failed to delete the blog.'
        ]);
    }
} else {
    // Just update the blog
    $update_blog = $conn->prepare("UPDATE blogs SET title = ?, content = ? WHERE blog_id = ?");
    $update_blog->bind_param('ssi', $title, $content, $blog_id);

    if ($update_blog->execute()) {
        echo json_encode([
            'status' => 'true',
            'message' => 'Blog updated successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'false',
            'message' => 'Failed to update blog.'
        ]);
    }
}
?>
