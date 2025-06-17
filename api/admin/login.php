<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
   response(false, "Invalid request method.");
}

// Ensure the data is coming from the request body
$data = json_decode(file_get_contents('php://input'), true);

// Sanitize and validate inputs
$email = clean_input($data["email"] ?? '');
$password = clean_input($data["password"] ?? '');

// Check if both email and password are provided
if (empty($email) || empty($password)) {
    response(false, "Email and password are required.");
}

// Query to fetch admin details based on the email
$stmt = $conn->prepare("SELECT admin_id, name, email, password, role FROM admins WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// If no admin with this email exists
if ($result->num_rows === 0) {
   response(false, "Invalid credentials.");
}

$admin = $result->fetch_assoc();

// Verify the provided password against the hashed password in the database
if (!password_verify($password, $admin["password"])) {
   response(false, "Incorrect password.");
}

// Generate JWT with correct payload (admin_id, email, role)
$token = generate_jwt($admin["admin_id"], $admin["email"], $admin["role"]);

// Send the response with the generated token and admin data
response(true, "Admin logged in successfully.", [ "token" => $token,
    "admin" => [
        "id" => $admin["admin_id"],
        "name" => $admin["name"],
        "email" => $admin["email"],
        "role" => $admin["role"] // Include role for clarity])
   
    ]
]);

// Close the statement and connection
$stmt->close();
$conn->close();
?>
