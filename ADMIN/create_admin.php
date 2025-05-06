<?php
require_once "../CONFIG/bootstrap.php";

header("Content-Type: application/json");



$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

// You might store tokens in a table `admin_tokens` or similar
if (empty($authHeader)) {
    echo json_encode(['status' => 'false', 'message' => 'Unauthorized. No token provided.']);
    exit;
}

$token = trim(str_replace('Bearer', '', $authHeader));

$verify_token_stmt = $conn->prepare("SELECT admin_id FROM admin_tokens WHERE token = ?");
$verify_token_stmt->bind_param("s", $token);
$verify_token_stmt->execute();
$verify_result = $verify_token_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['status' => 'false', 'message' => 'Unauthorized. Invalid token.']);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->prepare("SELECT * FROM admins WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "false", "message" => "Admin already exists"]);
    exit;
}

// Insert new admin
$stmt = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo json_encode(["status" => "true", "message" => "Admin created successfully"]);
} else {
    echo json_encode(["status" => "false", "message" => "Failed to create admin"]);
}
?>
