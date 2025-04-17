<?php 
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

$email = $_POST["email"] ?? null;

if (!$email) {
    echo json_encode(["status" => "error", "message" => "Email is required."]);
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id, name, email FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result(); 
if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found."]);
    exit;
}

$user = $result->fetch_assoc();

$otp = rand(100000, 999999);
$otp_expiration = date("Y-m-d H:i:s", strtotime('+10 minutes'));

// Save OTP to DB
$updateStmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiration = ? WHERE email = ?");
$updateStmt->bind_param("sss", $otp, $otp_expiration, $email);
$updateStmt->execute();

// Safety check on user's name
$name = $user['name'] ?? 'User';

$subject = "Password Reset OTP";
$message = "Hello $name,\n\nYour OTP for password reset is: $otp\nIt expires in 10 minutes.";

if (mail($email, $subject, $message)) {
    echo json_encode(["status" => "success", "message" => "OTP sent to your email."]);
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "Failed to send OTP. Please try again.",
        "debug" => [
            "email" => $email,
            "subject" => $subject,
            "message" => $message
        ]
    ]);
}

?>
