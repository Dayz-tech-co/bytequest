<?php  
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Response Method."
    ]);
    exit;
}

$admin_id = $_POST["admin_id"] ?? null;
$status = $_POST["status"] ?? null; // user account status (active, suspended, banned, etc.)
$role = $_POST["role"] ?? null;     // user role (user or admin)
$id = $_POST["id"] ?? null;         // user ID

if (!$admin_id || !$status || !$role || !$id) {
    echo json_encode([
        "status" => "error",
        "message" => "All fields (admin_id, status, role, and id) are required."
    ]);
    exit;
}

// Validate admin
$admin_stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();

if ($admin_result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized: admin not found."
    ]);
    exit;
}

$admin_data = $admin_result->fetch_assoc();
if ($admin_data["role"] !== "admin") {
    echo json_encode([
        "status" => "error",
        "message" => "Only admins are allowed to perform this action."
    ]);
    exit;
}

// Validate user existence
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found."
    ]);
    exit;
}

// Update user's status and role
$update_stmt = $conn->prepare("UPDATE users SET status = ?, role = ? WHERE id = ?");
$update_stmt->bind_param("ssi", $status, $role, $id);

if ($update_stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "User status and role updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update user."
    ]);
}
?>
