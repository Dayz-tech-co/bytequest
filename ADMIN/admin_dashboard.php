<?php
require "../vendor/autoload.php";
require "../CONFIG/bytequest_db.php";
require "../CONFIG/jwt_helper.php"; // Contains SECRET_KEY constant

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

// Read headers
$headers = apache_request_headers();

// Check for Authorization token
if (!isset($headers['Authorization'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Authorization token missing"
    ]);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace("Bearer ", "", $authHeader);

try {
    // Decode JWT
    $decoded = decode_jwt($token); // Use the decode function

    if ($decoded === null) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid or expired token"
        ]);
        exit;
    }

    // Check if the role is admin
    if (!isset($decoded['role']) || $decoded['role'] !== 'admin') {
        echo json_encode([
            "status" => "error",
            "message" => "Access denied: Admins only"
        ]);
        exit;
    }

    // All good, welcome admin!
    echo json_encode([
        "status" => "success",
        "message" => "Welcome to Admin Dashboard",
        "admin" => [
            "admin_id" => $decoded['admin_id'], // Corrected to 'admin_id'
            "email" => $decoded['email'],
            "role" => $decoded['role']
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid or expired token",
        "error" => $e->getMessage()
    ]);
}
