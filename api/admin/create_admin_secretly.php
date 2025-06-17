<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");



// Set a secret key for security
 $secret_key = $_ENV["JWT_SECRET"];

// Validate the secret key from the request
$key = isset($_GET["key"])? clean_input($_GET['key'] ) : null;

if ($key !== $secret_key) {
 response(false, "Unauthorized Access");
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
  response(false, "Admin already exists.");
}

// Insert admin
$stmt = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $password, $role);

if ($stmt->execute()) {
   response(true, "Admin created Successfully.");
} else {
   response(false, "Failed to create Admin.");
}
?>
