<?php
require_once "../CONFIG/bootstrap.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// 1. Get token from query string
if (!isset($_GET['token'])) {
    echo json_encode([
        "status" => "false",
        "message" => "Verification token missing"
    ]);
    exit;
}
$token = clean_input( $_GET['token']);

try {
    // 2. Decode the token
    $decoded = decode_jwt($token);

    if (!$decoded || !isset($decoded['email'])) {
        echo json_encode([
            "status" => "false",
            "message" => "Invalid or expired token"
        ]);
        exit;
    }

    $email = $decoded['email'];

    // 3. Update the database to mark email as verified
    $stmt = $conn->prepare("UPDATE users SET email_verified = 1 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status" => "true",
            "message" => "Email verified successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "false",
            "message" => "Email verification failed or already verified"
        ]);
    }

    $stmt->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "true",
        "message" => "Token verification failed",
        "error" => $e->getMessage()
    ]);
}
