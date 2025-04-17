<?php
require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

define('SECRET_KEY', 'your_secret_key_here'); // Use a strong key!
define('JWT_ALGO', 'HS256');

// Flexible token generator for both user and admin
function generate_jwt($id, $email, $role = 'user') {
    $issuedAt = time();
    $expirationTime = $issuedAt + 3600; // 1 hour expiry

    $payload = [
        ($role === 'admin' ? 'admin_id' : 'id') => $id,
        'email' => $email,
        'role' => $role,
        'iat' => $issuedAt,
        'exp' => $expirationTime
    ];

    return JWT::encode($payload, SECRET_KEY, JWT_ALGO);
}

// Decode JWT and return as array
function decode_jwt($token) {
    try {
        $decoded = JWT::decode($token, new Key(SECRET_KEY, JWT_ALGO));
        return (array) $decoded;
    } catch (Exception $e) {
        return null;
    }
}
