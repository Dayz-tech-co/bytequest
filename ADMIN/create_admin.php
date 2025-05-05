<?php
require_once '../CONFIG/bootstrap.php';
header("Content-Type: application/json");
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
// Receive POST data
$name = clean_input( $_POST['name']);
$email = clean_input( $_POST['email']);
$password = clean_input($_POST['password']);
$role = "admin";  // Default role for admin

// Validate input data
if (!$name || !$email || !$password) {
    echo json_encode(["status" => "false", "message" => "All fields are required."]);
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
