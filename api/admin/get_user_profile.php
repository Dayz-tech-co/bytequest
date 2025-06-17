<?php

require_once "../config/bootstrap.php";

header("Content-Type: application/json");

// 1. Get JWT token from the Authorization header
$headers = apache_request_headers();
$token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
$decoded = decode_jwt($token);

if (empty ($token)){
    response(false, "Authorization Token Missing");
}

// 2. Validate JWT and Admin Role
if (!$decoded || $decoded['role'] !== 'admin') {
    response(false, "Unauthorized: Access denied, Admin only.");
}

$admin_id = $decoded['admin_id'];

// 3. Validate if admin_id exists in the database
$verify_admin = $conn->prepare("SELECT admin_id FROM admins WHERE admin_id = ?");
$verify_admin->bind_param("i", $admin_id);
$verify_admin->execute();
$admin_result = $verify_admin->get_result();

if ($admin_result->num_rows === 0) {
    response(false, "Access denied. Invalid admin credentials.");
}



// 4. Validate user_id in the query parameter
if (!isset($_GET['id'])) {
    response(false, "Missing user_id in query");
}

$user_id_raw = clean_input($_GET['id']);

if (!is_numeric($user_id_raw)) {
    response(false, "Invalid user_id format");
}

$user_id = (int) $user_id_raw;  


// 5. Fetch the user profile
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
   response(false, "User not found");
} else {
    $user = $result->fetch_assoc();
    response(true, "User profile retrieved", ["user" => $user]);
}

// 6. Cleanup
$stmt->close();
$conn->close();

?>
