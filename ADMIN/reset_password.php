<?php 
require_once "../CONFIG/bootstrap.php";
require_once "../CONFIG/functions.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);


$email = clean_input($data["email"] ?? "");
$password = clean_input( $data["password"] ?? "");

if (empty ($email) || empty ($password)){
     echo json_encode([
        "status" => "false",
        "message" => "Email and Password are both required."
     ]);
     exit;
}
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$stmt=$conn->prepare("UPDATE admins SET password = ?, reset_otp = NULL, otp_expiry = NULL, WHERE email = ? ");
$stmt->bind_param("ss", $hashed_password, $email);

if ($stmt->execute()){
    echo json_encode([
        "status" => "true",
        "message" => "Password reset successful",
    ]);
} else {
    echo json_encode([
        "status" => "false",
        "message" => "Failed to Update Password",
    ]);
}
?>