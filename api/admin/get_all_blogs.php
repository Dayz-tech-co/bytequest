<?php
require_once "../config/bootstrap.php";
header("Content-Type: application/json");

// 1. AUTHENTICATE ADMIN
$headers = apache_request_headers();
$authHeader = $headers['Authorization'] ?? '';
$token = trim(str_replace('Bearer ', '', $authHeader));

if (empty($token)) {
    response(false, "Unauthorized: Token is missing");
}

// Decode the JWT token
$decoded = decode_jwt($token);

if (!$decoded || $decoded['role'] !== 'admin') {
    response(false, "Unauthorized access: Admin only.");
}

$admin_id = $decoded['admin_id'];

// Validate admin ID from database
$admin_check = $conn->prepare("SELECT admin_id FROM admins WHERE admin_id = ?");
$admin_check->bind_param("i", $admin_id);
$admin_check->execute();
$admin_result = $admin_check->get_result();

if ($admin_result->num_rows === 0) {
    response(false, "Invalid admin or token inserted.");
}

// 2. PAGINATION FOR BLOGS
$page = isset($_GET['page']) && is_numeric($_GET['page']) 
        ? max(1, (int) clean_input($_GET['page'])) 
        : 1;

$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) 
        ? max(1, (int) clean_input($_GET['limit'])) 
        : 10;

$offset = ($page - 1) * $limit;


// 3. FETCH BLOGS
$blog_stmt = $conn->prepare("SELECT blog_id, title, content, image, author_id FROM blogs ORDER BY blog_id DESC LIMIT ? OFFSET ?");
$blog_stmt->bind_param("ii", $limit, $offset);
$blog_stmt->execute();
$blog_result = $blog_stmt->get_result();

$blogs = [];

while ($blog = $blog_result->fetch_assoc()) {
    // 4. FETCH COMMENTS FOR EACH BLOG
    $comment_stmt = $conn->prepare("SELECT comment_id, user_id, comment_text, created_at FROM comments WHERE blog_id = ? ORDER BY created_at DESC");
    $comment_stmt->bind_param("i", $blog['blog_id']);
    $comment_stmt->execute();
    $comment_result = $comment_stmt->get_result();

    $comments = [];
    while ($comment = $comment_result->fetch_assoc()) {
        $comments[] = $comment;
    }

    $blog['comments'] = $comments;
    $blogs[] = $blog;

    $comment_stmt->close();
}

// 5. TOTAL BLOG COUNT FOR PAGINATION
$countResult = $conn->query("SELECT COUNT(*) AS total FROM blogs");
$totalBlogs = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalBlogs / $limit);

// 6. RESPONSE
response(true, "Blogs with Comments Fetched Successfully", [
    "current_page" => $page,
    "per_page" => $limit,
    "total_blogs" => $totalBlogs,
    "total_pages" => $totalPages,
    "data" => $blogs
]);

$blog_stmt->close();
$conn->close();
?>
