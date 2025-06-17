<?php
require_once "../config/bootstrap.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type/ application/json");

// Read headers
$headers = apache_request_headers();

$authHeader = $headers['Authorization'];
$token = str_replace("Bearer ", "", $authHeader);

if (empty ($token)){
    response(false, "Authorization Token Missing");
}

try {
    // Decode JWT
    $decoded = decode_jwt($token); // Use the decode function

    if ($decoded === null) {
        response(false, "Invalid or expired token.");
    }

    // Check if the role is admin
    if (!isset($decoded['role']) || $decoded['role'] !== 'admin') {
        response(false, "Access denied. Admin only.");
    }

    // All good, welcome admin!
    response(true, "Welcome to Admin dashboard", [
        "admin" => [
            "admin_id" => $decoded['admin_id'],
            "email" => $decoded['email'],
            "role" => $decoded['role']
        ]
    ]);

} catch (Exception $e) {
    response(false, "Invalid or expired token");
}
