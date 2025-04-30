<?php
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

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
        "status" => "success",
        "message" => "Blogs fetched successfully.",
        "current_page" => $page,
        "per_page" => $limit,
        "total_blogs" => $totalBlogs,
        "total_pages" => $totalPages,
        "data" => $blogs
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch blogs."
    ]);
}

$stmt->close();
$conn->close();
?>
