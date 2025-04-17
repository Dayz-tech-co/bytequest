<?php
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

$email = $_POST["email"] ?? null;
$otp = $_POST["otp"] ?? null;

if (!$email || !$otp) {
    echo json_encode(["status" => "error", "message" => "Email and OTP are required."]);
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

echo json_encode(["status" => "success", "message" => "OTP verified successfully. You can now proceed to update your password"]);
?>
