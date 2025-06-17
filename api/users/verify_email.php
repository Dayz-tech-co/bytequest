<?php
require_once "../config/bootstrap.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// 1. Get token from query string
if (!isset($_GET['token'])) {
  response(false, "Verification token missing");
}
$token = clean_input( $_GET['token']);

try {
    // 2. Decode the token
    $decoded = decode_jwt($token);

    if (!$decoded || !isset($decoded['email'])) {
       response(false, "Invalid or expired token");
    }

    $email = $decoded['email'];

// Check if the username or email already exists
$check_stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
$check_stmt->bind_param("ss", $username, $email);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['username'] === $username) {
        response(true, "Username already exists.");
    } else {
       response(false, "Email already exists.");
    }
}

    // 3. Update the database to mark email as verified
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
    if ($stmt->affected_rows > 0) {
        response(true,  "Email verified successfully");
    } else {
        response(false, "Email verification failed or already verified");
    }

    $stmt->close();

} catch (Exception $e) {
   response(false, "Token Verification Failed");
}
