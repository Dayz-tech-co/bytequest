<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$email = isset($_POST["email"])? clean_input($data["email"]) : null;  // Use data from the decoded JSON

if (empty($email)){
   response(false, "Email is required.");
}

// Check if admin exists
$stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    response(false, "unauthorized: Admin not found");
}

$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Save OTP in the database
$update_stmt = $conn->prepare("UPDATE admins SET reset_otp = ?, otp_expiry = ? WHERE email = ?");
$update_stmt->bind_param("sss", $otp, $expiry, $email); 
$update_stmt->execute();

if ($update_stmt->affected_rows > 0) {
   response(true, "Otp sent to admin email".  ["otp" => $otp]);
} else {
   response(false, "Failed to send Otp");
}
?>
