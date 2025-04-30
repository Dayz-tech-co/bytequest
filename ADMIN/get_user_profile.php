<?php
require_once '../CONFIG/bytequest_db.php';
require_once '../CONFIG/jwt_helper.php';

header("Content-Type: application/json");

// 1. Authorization token
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(["error" => "Authorization token not found."]);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

// 2. Decode JWT
$decoded = decode_jwt($token);
if (!$decoded || !isset($decoded['admin_id']) || $decoded['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// 3. Validate user ID sent via query (?user_id=2)
if (!isset($_GET['user_id'])) {
    echo json_encode(["error" => "Missing user_id in query"]);
    exit;
}

$user_id = (int) $_GET['user_id'];

// 4. Fetch the user profile
$stmt = $conn->prepare("SELECT user_id, name, email, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "User not found"]);
} else {
    $user = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "message" => "User profile retrieved",
        "data" => $user
    ]);
}

$stmt->close();
$conn->close();
?>
