<?php 
require_once "../config/bootstrap.php";
header("Content-Type: application/json");

// 1. Clean Inputs
$otp = isset($_POST["otp"]) ? clean_input($_POST["otp"]) : null;
$new_password = isset($_POST["new_password"]) ? clean_input($_POST["new_password"]) : null;

// 2. Validate Inputs
if (empty($otp) || empty($new_password)) {
 response(false, "Both OTP and new password are required.");
}

// 3. Check OTP Record
$stmt = $conn->prepare("SELECT email, otp_expiry FROM admins WHERE otp = ?");
$stmt->bind_param("s", $otp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
   response(false, "Invalid or expired OTP.");
}

$data = $result->fetch_assoc();
$email = $data["email"];
$expires_at = strtotime($data["otp_expiry"]);

if (time() > $expires_at) {
   response(false, "OTP expired. Request a new one.");
}

// 4. Hash Password and Update
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE admins SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
$update->bind_param("ss", $hashed_password, $email);

if ($update->execute()) {
   response(true, "Admin password reset successful.");
} else {
  response(false, "Something went wrong during password reset.");
}
?>
