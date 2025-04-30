<?php
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = clean_input($data["email"] ?? '');
$otp = clean_input($data["otp"] ?? '');

if (empty($email) || empty($otp)) {
    echo json_encode(["status" => "error", "message" => "Email and OTP are required."]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND reset_otp = ?");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid OTP."]);
    exit;
}

$row = $result->fetch_assoc();

if (strtotime($row["otp_expiry"]) < time()) {
    echo json_encode(["status" => "error", "message" => "OTP expired."]);
    exit;
}

echo json_encode(["status" => "success", "message" => "OTP verified Successfully. You can now proceed to update your password"]);
?>