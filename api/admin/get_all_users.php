<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';  // Use null coalescing to avoid error
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token)) {
    response(false, "Authorization token not found.");
}

// 2. Decode the JWT
$decoded = decode_jwt($token);

// Check for decoding errors
if (!$decoded || isset($decoded['error'])) {
  response(false, "Invalid or expired token");
}

$admin_id = $decoded['admin_id'];
$email = $decoded['email'];

// 3. Check if it's really an admin
if (!$decoded || $decoded['role'] !== 'admin') {
   response(false, "Unauthorized: Admin access required");
}

// 4. Verify admin identity in DB
$stmt = $conn->prepare("SELECT * FROM admins WHERE admin_id = ? AND email = ?");
$stmt->bind_param("is", $admin_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  response(false,  "Admin not found.");
}

// 5. Pagination values

$page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? max(1, (int) clean_input($_GET['page'])) 
        : 1;

$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) 
        ? max(1, (int) clean_input($_GET['limit'])) 
        : 10;

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
response(true, "Users fetched Successfully.", ["current_page" => $page,
    "per_page" => $limit,
    "total_users" => $totalUsers,
    "total_pages" => $totalPages,
    "data" => $users] );

// 9. Cleanup
$stmt->close();
$userStmt->close();
$conn->close();
?>
