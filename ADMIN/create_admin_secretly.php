<?php
require_once "../CONFIG/bootstrap.php";

header("Content-Type: application/json");



// Set a secret key for security
 $secret_key = $_ENV["JWT_SECRET"];

// Validate the secret key from the request
$key = $_GET['key'] ?? null;

if ($key !== $secret_key) {
    echo json_encode(["status" => "false", "message" => "Unauthorized access"]);
    exit;
}
// Allowing just my device to run the script
// $allowed_ip = '192.168.182.58'; // replace with YOUR IPv4 address
// $client_ip = $_SERVER['REMOTE_ADDR'];

// if ($client_ip !== $allowed_ip) {
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Access denied. IP not allowed.'
//     ]);
//     exit;
// }

// Simulate admin data (you can later change this to $_POST if needed)
$name = "oduola"; // or get from $_POST
$email = "ZaydAdmin@Byte.blog";
$password = password_hash("admin123", PASSWORD_DEFAULT); // Hashed password
$role = "admin";

// Check if admin already exists
$check = $conn->prepare("SELECT * FROM admins WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "false", "message" => "Admin already exists"]);
    exit;
}

// Insert admin
$stmt = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
    echo json_encode(["status" => "true", "message" => "Admin created successfully"]);
} else {
    echo json_encode(["status" => "false", "message" => "Failed to create admin"]);
}
?>
