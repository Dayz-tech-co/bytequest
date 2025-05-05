<?php
require_once './CONFIG/bootstrap.php';
require_once '../CONFIG/jwt_helper.php';

$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$decoded = decode_jwt($token);

if (!$decoded || $decoded['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
$admin_id = $decoded['admin_id'];

// Validate user_id to delete
$user_id = isset($_POST['user_id']) ? clean_input((int)$_POST['user_id'] ): null;

if (!$user_id) {
    echo json_encode(["error" => "User ID is required"]);
    exit;
}

// Check if user exists before deletion
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

// Proceed to delete
$del = $conn->prepare("DELETE FROM users WHERE id = ?");
$del->bind_param("i", $user_id);
$del->execute();

echo json_encode(["message" => "User deleted successfully"]);
?>