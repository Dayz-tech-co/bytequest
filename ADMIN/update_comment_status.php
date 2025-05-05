<?php
require_once "./CONFIG/bootstrap.php";
require_once "../CONFIG/jwt_helper.php";
require_once "../CONFIG/functions.php";

header("Content-Type: application/json");

// 1. Authorization Header Check
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(["status" => "error", "message" => "Authorization token not found."]);
    exit;
}
$token = str_replace("Bearer ", "", $headers["Authorization"]);
$decoded = decode_jwt($token);

if (!$decoded || isset($decoded["error"]) || $decoded["role"] !== "admin") {
    echo json_encode(["status" => "false", "message" => "Unauthorized access. Admin only."]);
    exit;
}

$admin_id = $decoded["admin_id"];

// 2. Input Handling
$comment_id = clean_input($_POST["comment_id"] ?? '');
$status = clean_input($_POST["status"] ?? '');

if (!$comment_id || !$status) {
    echo json_encode(["status" => "false", "message" => "Comment ID and status are required."]);
    exit;
}

// 3. Status Validation (comments only)
$valid_statuses = ['pending', 'approved', 'rejected'];
if (!in_array($status, $valid_statuses)) {
    echo json_encode(["status" => "false", "message" => "Invalid status. Must be 'pending', 'approved', or 'rejected'."]);
    exit;
}

// 4. Check if the comment exists
$stmt = $conn->prepare("SELECT * FROM comments WHERE comment_id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "false", "message" => "Comment not found."]);
    exit;
}

// 5. Update comment status
$update = $conn->prepare("UPDATE comments SET status = ? WHERE comment_id = ?");
$update->bind_param("si", $status, $comment_id);

if ($update->execute()) {
    echo json_encode(["status" => "true", "message" => "Comment status updated successfully."]);
} else {
    echo json_encode(["status" => "false", "message" => "Failed to update comment."]);
}
?>
