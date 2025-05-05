<?php 
include "./CONFIG/bootstrap.php";
header("Content-Type: application/json");


function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Retrieve user credentials
$email = clean_input($_POST['email']);
$password = trim($_POST['password']); // password: trim only, no htmlspecialchars
$name = clean_input($_POST['name']);
$username= clean_input($_POST["username"]);
$role = clean_input($_POST["role"]);

if (!$name || !$username || !$email || !$password || !$role){
    echo json_encode(["status" => "false", "message" => "All fields are required."]);
    exit;
}

// Check if the username or email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0){
    echo json_encode(["status" => "false", "message" => "Username or Email already exists."]);
    exit;
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user into the database
$stmt = $conn->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $username, $email, $hashedPassword, $role);

if ($stmt->execute()){
    echo json_encode(["status" => "true", "message" => "User registered successfully."]);
} else {
    echo json_encode(["status" => "false", "message" => "Registration failed."]);
}
?>
