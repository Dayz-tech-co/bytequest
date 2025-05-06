<?php
require_once "../CONFIG/bootstrap.php";

header("Content-Type: application/json");

// 1. Get token from Authorization header
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    echo json_encode(["error" => "Authorization token not found."]);
    exit;
}

$authHeader = $headers['Authorization'];
$token = str_replace('Bearer ', '', $authHeader);

// 2. Decode the JWT
$decoded = decode_jwt($token);

if (!$decoded || isset($decoded['error'])) {
    echo json_encode(["error" => "Invalid or expired token"]);
    exit;
}

// 3. Check if it's really an admin
if (!isset($decoded['admin_id']) || $decoded['role'] !== 'admin') {
    echo json_encode(["error" => "Unauthorized: Admin access required"]);
    exit;
}

$admin_id = $decoded['admin_id'];
$email = $decoded['email'];

// 4. Verify admin identity in DB
$stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ? AND email = ?");
$stmt->bind_param("is", $admin_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "Admin not found."]);
    exit;
}

// 5. Pagination values
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$limit = isset($_GET['limit']) ? max((int) $_GET['limit'], 1) : 10;
$offset = ($page - 1) * $limit;

// 6. Fetch users
$userStmt = $conn->prepare("SELECT user_id, name, email, created_at FROM users ORDER BY user_id DESC LIMIT ? OFFSET ?");
$userStmt->bind_param("ii", $limit, $offset);
$userStmt->execute();
$userResult = $userStmt->get_result();

$users = [];
while ($row = $userResult->fetch_assoc()) {
    $users[] = $row;
}

// 7. Get total count for pagination
$countResult = $conn->query("SELECT COUNT(*) AS total FROM users");
$totalUsers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

// 8. Respond with data
echo json_encode([
    "status" => "true",
    "message" => "Users fetched successfully",
    "current_page" => $page,
    "per_page" => $limit,
    "total_users" => $totalUsers,
    "total_pages" => $totalPages,
    "data" => $users
]);

// 9. Cleanup
$stmt->close();
$userStmt->close();
$conn->close();
?>
