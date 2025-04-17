<?php
include "../CONFIG/bytequest_db.php";

// Ensure no extra output is made
ob_start(); // Start output buffering

header("Content-Type: application/json");

$email = $_POST["email"] ?? null;
$new_password = $_POST["new_password"] ?? null;
$otp = $_POST["otp"] ?? null;

if (!$email || !$new_password || !$otp) {
    echo json_encode(["status" => "error", "message" => "Email, OTP, and new password are required."]);
    exit;
}

// Prepare the SQL query to find the user with the provided email
$stmt = $conn->prepare("SELECT otp, otp_expiration FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found."]);
    exit;
}

$user = $result->fetch_assoc();

// Check if OTP matches
if ($otp !== $user['otp']) {
    echo json_encode(["status" => "error", "message" => "Invalid OTP."]);
    exit;
}

// Check if OTP has expired
$expirationTime = strtotime($user['otp_expiration']);
if (time() > $expirationTime) {
    echo json_encode(["status" => "error", "message" => "OTP has expired."]);
    exit;
}

// Update the password (hash the password before saving)
$new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param("ss", $new_password_hashed, $email);
$stmt->execute();

echo json_encode(["status" => "success", "message" => "Password updated successfully."]);

// End output buffering to prevent unwanted output
ob_end_flush();
?>
