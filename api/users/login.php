<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

// Validate if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  response(false, 'Invalid request method. POST required');
}

// Get email and password from POST data
$email = isset($_POST["email"]) ? clean_input($_POST["email"]) : null;  // Use $_POST for form data
$password = trim($_POST['password'] ?? '');     // Trim only,

// Validate input
if (!$email || !$password) {
   response(false, 'Email and password are required');
}

// Prepare the SQL query to check user credentials
$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verify password and generate JWT token
if ($user && password_verify($password, $user['password'])) {
    // Generate JWT token with expiration
    $token = generate_jwt($user['id'], $user['role']);
    response(true, "Token Generated Successfully", ['token' => $token]);
} else {
  response(false, 'Invalid credentials');
}
?>
