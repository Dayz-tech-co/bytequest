<?php  
require_once "../config/bootstrap.php";
header("Content-Type: application/json");

$email = clean_input(trim($_POST["email"]));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    response(false, "Invalid email format.");
}


// 1. Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    // 2. Generate OTP
    $otp = rand(100000, 999999);

    // 3. Update user's OTP with expiration time (e.g., valid for 10 minutes)
    $expiration_time = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP expires in 10 minutes
    $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiration = ? WHERE id = ?");
    $update->bind_param("isi", $otp, $expiration_time, $user_id);
    
    if ($update->execute()) {
        response(true, "OTP sent successfully.", ["otp" => $otp]);
    } else {
      response(false, "Failed to update OTP in the database.");
    }
} else {
    response(false, "No user found with that email. Cannot send OTP.");
}
?>
