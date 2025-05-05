<?php  
require_once "../CONFIG/bootstrap.php";
require_once "../CONFIG/functions.php";

header("Content-Type: application/json");

// 1. Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Response Method."
    ]);
    exit;
}

// 2. Get and validate inputs
$admin_id = clean_input($decoded["admin_id"] ?? null);
$status = clean_input($_POST["status"] ?? null);  // user account status (active, suspended, banned)
$role = clean_input($_POST["role"] ?? null);      // user role (user or admin)
$id = clean_input($_POST["id"] ?? null);          // user ID

// Check if all fields are provided
if (!$admin_id || !$status || !$role || !$id) {
    echo json_encode([
        "status" => "false",
        "message" => "All fields (admin_id, status, role, and id) are required."
    ]);
    exit;
}

// 3. Validate status field (must be one of 'active', 'suspended', or 'banned')
if (!in_array($status, ['active', 'suspended', 'banned'])) {
    echo json_encode([
        "status" => "false",
        "message" => "Invalid status. Allowed values are 'active', 'suspended', or 'banned'."
    ]);
    exit;
}

// 4. Validate role field (must be either 'user' or 'admin')
if (!in_array($role, ['user', 'admin'])) {
    echo json_encode([
        "status" => "false",
        "message" => "Invalid role. Allowed values are 'user' or 'admin'."
    ]);
    exit;
}

// 5. Validate admin authentication
$admin_stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result = $admin_stmt->get_result();

if ($admin_result->num_rows === 0) {
    echo json_encode([
        "status" => "false",
        "message" => "Unauthorized: admin not found."
    ]);
    exit;
}

$admin_data = $admin_result->fetch_assoc();
if ($admin_data["role"] !== "admin") {
    echo json_encode([
        "status" => "false",
        "message" => "Only admins are allowed to perform this action."
    ]);
    exit;
}

// 6. Validate user existence
$user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_stmt->bind_param("i", $id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    echo json_encode([
        "status" => "false",
        "message" => "User not found."
    ]);
    exit;
}

// 7. Update user's status and role in the database
$update_stmt = $conn->prepare("UPDATE users SET status = ?, role = ? WHERE id = ?");
$update_stmt->bind_param("ssi", $status, $role, $id);

if ($update_stmt->execute()) {
    echo json_encode([
        "status" => "true",
        "message" => "User status and role updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "false",
        "message" => "Failed to update user."
    ]);
}
?>
