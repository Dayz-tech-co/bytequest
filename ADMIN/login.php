<?php
require_once './CONFIG/bootstrap.php';
require_once '../CONFIG/jwt_helper.php';
require_once "../CONFIG/functions.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "false", "message" => "Invalid request method."]);
    exit;
}



$data = json_decode(file_get_contents("php://input"), true);

$email = clean_input($data["email"] ?? '');
$password = clean_input( $data["password"] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and password are required."]);
    exit;
}

// Query for fetching admin details
$stmt = $conn->prepare("SELECT admin_id, name, email, password, role FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "false", "message" => "Invalid credentials."]);
    exit;
}

$admin = $result->fetch_assoc();

// Password verification
if (!password_verify($password, $admin["password"])) {
    echo json_encode(["status" => "false", "message" => "Incorrect password."]);
    exit;
}


// Generate JWT with correct payload
$token = generate_jwt($admin["admin_id"], $admin["email"], $admin["role"]);

// Now send the response with the generated token
echo json_encode([
    "status" => "true",
    "message" => "Admin logged in successfully.",
    "token" => $token,
    "admin" => [
        "id" => $admin["admin_id"],
        "name" => $admin["name"],
        "email" => $admin["email"],
        "role" => $admin["role"] // Include role in response for clarity
    ]
]);

?>