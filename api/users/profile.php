<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

// Fetch the token from Authorization header
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
    response(false, 'Token missing');
}

$token = str_replace('Bearer ', '', $authHeader);
$decoded = decode_jwt($token);

if (!$decoded) {
  response(false, 'Invalid or expired token');
}

// Determine user/admin ID and role
if (isset($decoded['admin_id'])) {
    $user_id = $decoded['admin_id'];
    $role = 'admin';
} elseif (isset($decoded['id'])) {
    $user_id = $decoded['id'];
    $role = 'user';
} else {
    response(false, 'Invalid token: ID not found');
}

// Query based on role
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT id AS admin_id, name, email FROM admins WHERE id = ?");
} elseif ($role === 'user') {
    $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
} else {
   response(false, 'Invalid role');
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
   response(true, "User", ['data' => $user]);
} else {
    response(false, ucfirst($role) . ' not found');
}
?>
