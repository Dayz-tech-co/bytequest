<?php 
require_once "../CONFIG/bootstrap.php";

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$email = clean_input($_POST["email"]);

if (empty($email)){
    echo json_encode([
        "status" => "false",
        "message" => "Email is required."
    ]);
    exit;
}

// Check if admin exist
$stmt=$conn->prepare("SELECT * FROM admins WHERE email = ? ");
$stmt->bind_param("s", $email);
$stmt->execute();
$result=$stmt->get_result();

if ($result->num_rows === 0){
    echo json_encode([
        "status"=> "false",
        "message" => "Admin not found."
    ]);
    exit;
}
$otp = rand(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

// Save otp
$update_stmt=$conn->prepare("UPDATE admins SET reset_otp = ?, otp_expiry = ? WHERE email = ?");
$update_stmt->bind_param("s", $email);
$update_stmt->execute();

echo json_encode([
    "status" => "true",
    "message" => "OTP sent to admin email.",
    "otp" => $otp  
]);
?>