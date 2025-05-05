<?php 
require_once "./CONFIG/bootstrap.php";
require_once "../CONFIG/functions.php";
header("Content-Type: application/json");


$email = clean_input(trim($_POST["email"]) );

// Check if user exists
$stmt=$conn->prepare("SELECT FROM id WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows===1){
     // Generate 6-digit OTP
     $otp = rand(100000, 999999);
     $created_at = date("Y-m-d H:i:s");
     // Optional: Remove previous OTPs for this email
     $conn->query("DELETE FROM users WHERE email = '$email' ");
    
    //  INSERT NEW OTP
    $insert=$conn->prepare("INSERT INTO users (email, otp, created_at) VALUES (?, ?, ?) ");
    $insert->bind_param("sss", $email, $otp, $created_at );
    $insert->execute();

    echo json_encode([
        "status" => "true",
        "message" => "Otp sent",
        "otp" => $otp,
    ]); 
} else {
    json_encode([
        "status" => "false",
        "message" => "Failed to send otp Due to email not found."
    ]);
}

?>