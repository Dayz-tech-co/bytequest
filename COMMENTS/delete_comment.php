<?php
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

// Check if data is being sent via POST
$data = json_decode(file_get_contents("php://input"), true); // Ensure you're decoding JSON properly

$comment_id = clean_input($data['comment_id'] ?? null); // Retrieve the comment ID

// Check if comment_id is provided
if (!$comment_id) {
    echo json_encode([
        "status" => "error",
        "message" => "comment_id is required."
    ]);
    exit;
}

// Prepare the SQL query to delete the comment
$stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);

// Execute the query
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Comment deleted successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete comment."
    ]);
}
?>
