<?php
require_once "../config/bootstrap.php";
header("Content-Type: application/json");

$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$decoded = decode_jwt($token);

if (empty ($token)){
   response(false, "Authorization Token Missing");
}

if (!$decoded || ($decoded['role'] ?? '') !== 'admin') {
  response(false, "Unauthorized access, Admin only.");
}

//Proceed with logic (example for creating admin)
$name = isset ($_POST["name"]) ? clean_input($_POST["name"] ) : null;
$email = isset  ($_POST['email']) ?  clean_input($_POST["email"] ) : null;
$password =   isset ($_POST["password"]) ?   clean_input($_POST["password"] ) : null;
$role =   isset ($_POST["role"]) ?   clean_input($_POST["role"] ): 'admin';

// Validate inputs
if (!$name || !$email || !$password) {
   response(false, "All fields (name, email, password) are required.");
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->prepare("SELECT * FROM admins WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
   response(false, "Admin already exists.");
}

// Insert new admin
$stmt = $conn->prepare("INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
response(true, "Admin created Successfully.");
} else {
   response(false, "Failed to create Admin.");
}
?>
