<?php 
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

// Retrieve user credentials
$name = $_POST["name"] ?? null;
$username = $_POST["username"] ?? null;
$email = $_POST["email"] ?? null;
$password = $_POST["password"] ?? null;
$role = $_POST["role"] ?? null;

if (!$name || !$username || !$email || !$password || !$role){
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit;
}

// Check if the username or email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0){
    echo json_encode(["status" => "error", "message" => "Username or Email already exists."]);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user into the database
$stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $username, $email, $hashedPassword, $role);

if ($stmt->execute()){
    echo json_encode(["status" => "success", "message" => "User registered successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed."]);
}
?>
