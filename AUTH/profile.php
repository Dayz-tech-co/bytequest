<?php
require_once "../CONFIG/bootstrap.php";

header("Content-Type: application/json");

// Fetch the token from Authorization header
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    echo json_encode(['status' => 'false', 'message' => 'Token missing']);
    exit;
}

$token = str_replace('Bearer ', '', $authHeader);
$decoded = decode_jwt($token);

if (!$decoded) {
    echo json_encode(['status' => 'false', 'message' => 'Invalid or expired token']);
    exit;
}
// Extract user/admin ID
$user_id = $decoded['id'] ?? $decoded['admin_id'] ?? null;
$role = $decoded['role'] ?? 'user';

if (!$user_id) {
    echo json_encode(['status' => 'false', 'message' => 'Invalid token: ID not found']);
    exit;
}

// Query based on role
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id AS admin_id, name, email FROM admins WHERE id = ?");
} else {
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    echo json_encode(['status' => 'true', 'data' => $user]);
} else {
    echo json_encode(['status' => 'false', 'message' => ucfirst($role) . ' not found']);
}
