<?php
require_once "../config/bootstrap.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// 1. Get token from query string
if (!isset($_GET['token'])) {
   response(false, "Verification token missing");
}

$token = isset ($_POST["token"])? clean_input($_GET['token']) : null;

try {
    // 2. Decode the token
    $decoded = decode_jwt($token);

    if (!$decoded || !isset($decoded['email'])) {
          response(false, "Invalid or expired token");
    }

    $email = $decoded['email'];

    // Optional: Validate if admin is performing the verification (this is an additional security check)
    if (!isset($decoded['role']) || $decoded['role'] !== 'admin') {
       response(false, "Unauthorized: Only admins can verify emails");
    }

    // 3. Check if the email exists before proceeding
    $stmt = $conn->prepare("SELECT email_verified FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        response(false,  "Email not found");
    }

    // 4. Update the database to mark email as verified
    $stmt = $conn->prepare("UPDATE admins SET email_verified = 1 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
      response(true, "Email verified successfully");
       
    } else {
        response(false, "Email verification failed or already verified");
    }

    $stmt->close();

} catch (Exception $e) {
    response(false, "Token Verification Failed");
}
?>
