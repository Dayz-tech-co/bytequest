<?php
include '../CONFIG/bytequest_db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blog_id = $_POST['blog_id'] ?? '';
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if (empty($blog_id) || empty($title) || empty($content)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'blog_id, title and content are required.'
        ]);
        exit;
    }

    $query = "UPDATE blogs SET title = ?, content = ? WHERE blog_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param('ssi', $title, $content, $blog_id);
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Blog post updated successfully'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update blog post'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Query error'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
