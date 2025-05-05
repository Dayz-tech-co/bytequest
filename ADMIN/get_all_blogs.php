<?php
include "./CONFIG/bootstrap.php";
header("Content-Type: application/json");

$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';

// You might store tokens in a table `admin_tokens` or similar
if (empty($authHeader)) {
    echo json_encode(['status' => 'false', 'message' => 'Unauthorized. No token provided.']);
    exit;
}

$token = trim(str_replace('Bearer', '', $authHeader));

$verify_token_stmt = $conn->prepare("SELECT admin_id FROM admin_tokens WHERE token = ?");
$verify_token_stmt->bind_param("s", $token);
$verify_token_stmt->execute();
$verify_result = $verify_token_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['status' => 'false', 'message' => 'Unauthorized. Invalid token.']);
    exit;
}


// Sanitize & get page/limit from query parameters
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$limit = isset($_GET['limit']) ? max((int) $_GET['limit'], 1) : 10;
$offset = ($page - 1) * $limit;

// Prepare paginated SQL query
$stmt = $conn->prepare("SELECT blog_id, title, content, image, author_id FROM blogs ORDER BY blog_id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);

// Execute query
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $blogs = [];

    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }

    // Optional: Get total number of blogs for frontend pagination
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM blogs");
    $totalBlogs = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalBlogs / $limit);

    echo json_encode([
        "status" => "true",
        "message" => "Blogs fetched successfully.",
        "current_page" => $page,
        "per_page" => $limit,
        "total_blogs" => $totalBlogs,
        "total_pages" => $totalPages,
        "data" => $blogs
    ]);
} else {
    echo json_encode([
        "status" => "false",
        "message" => "Failed to fetch blogs."
    ]);
}

$stmt->close();
$conn->close();
?>
