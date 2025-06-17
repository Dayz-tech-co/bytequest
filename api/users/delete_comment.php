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

if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
    response(false, "Invalid Request Method");
}

$data = json_decode(file_get_contents("php://input"), true);
$comment_id = clean_input($data['comment_id'] ?? null);

if (!$comment_id) {
    response(false, "comment_id is required.");
}

// ✅ Secure deletion: Delete only if the comment belongs to the user
$stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ? AND author_id = ?");
if ($stmt === false) {
    response(false, "Failed to prepare the delete query.");
}

$stmt->bind_param("ii", $comment_id, $author_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        response(true, "Comment deleted successfully.");
    } else {
        response(false, "Comment does not exist or does not belong to you.");
    }
} else {
    response(false, "Failed to delete comment", ["error" => $stmt->error]);
}
?>
