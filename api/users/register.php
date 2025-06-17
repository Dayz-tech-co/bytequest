<?php 
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

// Retrieve user credentials
$email = isset($_POST["email"]) ? clean_input($_POST['email']): null;
$password = trim($_POST['password']); // password: trim only, no htmlspecialchars
$name =isset($_POST["name"])? clean_input($_POST['name']) : null;
$username = isset($_POST["username"]) ? clean_input($_POST['username']) : null;

// Validate that all fields are filled
if (!$name || !$username || !$email || !$password) {
   response(false,  "All fields are required.");
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    response(false, "Invalid email format.");
}

// Check if the username or email already exists
$stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['username'] === $username) {
        response(true, "Username already exists.");
    } else {
       response(false, "Email already exists.");
    }
}

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert user into the database
$stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $username, $email, $hashedPassword);

if ($stmt->execute()) {
   response(true, "User registered successfully.");
} else {
response(false, "Registration failed.");
}
?>
