<?php 
require_once "../CONFIG/bootstrap.php";

header("Content-Type: application/json");



$email = clean_input(trim($_POST["email"]));
$otp = clean_input(trim($_POST["otp"]));
$new_password = clean_input(trim($_POST["new_password"]));

// Verify OTP
$stmt=$conn->prepare("SELECT * FROM  users WHERE email =? AND otp =? ");
$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows==1){
    // HASH NEW PASSWORD

    $hashed_password=password_hash($password, PASSWORD_DEFAULT);

     // Update user's password
     $update=$conn->prepare("UPDATE users SET password = ? WHERE email = ?");
     $update->bind_param("ss", $hashed_password, $email);
     $update->execute();

     // Delete OTP
    $conn->query("DELETE FROM password_resets WHERE email = '$email'");
    
    echo json_encode([
        "status" => "true",
        "message" => "Password reset successfully",
    ]);
} else {
    echo json_encode([
        "status" => "false",
        "message" => "Failed to update password",
    ]);
}

?>