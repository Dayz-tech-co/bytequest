<?php
require_once '../CONFIG/bytequest_db.php';
header("Content-Type: application/json");

// Receive POST data
$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$role = "admin";  // Default role for admin

// Validate input data
if (!$name || !$email || !$password) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
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
    echo json_encode(["status" => "error", "message" => "Admin already exists"]);
    exit;
}

// Insert new admin
$stmt = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Admin created successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to create admin"]);
}
?>
