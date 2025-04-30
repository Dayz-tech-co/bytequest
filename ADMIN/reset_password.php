<?php 
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$email = clean_input($data["email"] ?? "");
$password = clean_input( $data["password"] ?? "");

if (empty ($email) || empty ($password)){
     echo json_encode([
        "status" => "error",
        "message" => "Email and Password are both required."
     ]);
     exit;
}
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt=$conn->prepare("UPDATE admins SET password = ?, reset_otp = NULL, otp_expiry = NULL, WHERE email = ? ");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "Password reset successful",
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to Update Password",
    ]);
}
?>