<?php
header('Content-Type: application/json');
include "../CONFIG/bytequest_db.php";

// Debugging: Uncomment to view the raw POST
// echo '<pre>'; print_r($_POST); echo '</pre>'; exit;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

$comment_id = isset($_POST['comment_id']) ? $_POST['comment_id'] : null;
$status = isset($_POST['status']) ? $_POST['status'] : null;
$admin_id = isset($_POST['admin_id']) ? $_POST['admin_id'] : null;

if (!$comment_id || !$status || !$admin_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'All fields (comment_id, status, admin_id) are required.'
    ]);
    exit;
}

// Check if admin exists
$admin_query = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_query->bind_param("i", $admin_id);
$admin_query->execute();
$admin_result = $admin_query->get_result();

if ($admin_result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Access denied. Only admins can update comment status.'
    ]);
    exit;
}

// Update comment status
$update_query = $conn->prepare("UPDATE comments SET status = ? WHERE comment_id = ?");
$update_query->bind_param("si", $status, $comment_id);

if ($update_query->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Comment status updated successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update comment status.'
    ]);
}
?>
