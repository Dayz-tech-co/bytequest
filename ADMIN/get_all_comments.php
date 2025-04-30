<?php
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

// Step 1: Handle pagination input safely
$page = isset($_GET['page']) ? max((int) $_GET['page'], 1) : 1;
$limit = isset($_GET['limit']) ? max((int) $_GET['limit'], 1) : 10;
$offset = ($page - 1) * $limit;

// Step 2: Prepare and execute the paginated SQL query
$stmt = $conn->prepare("SELECT * FROM comments ORDER BY comment_id DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $comments = [];

    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    // Step 3: Get total number of comments for pagination metadata
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM comments");
    $totalComments = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalComments / $limit);

    // Step 4: Send JSON response
    echo json_encode([
        "status" => "success",
        "message" => "Comments fetched successfully.",
        "current_page" => $page,
        "per_page" => $limit,
        "total_comments" => $totalComments,
        "total_pages" => $totalPages,
        "data" => $comments
    ]);
} else {
    // Step 5: Handle execution failure
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch comments."
    ]);
}

// Step 6: Clean up
$stmt->close();
$conn->close();
?>
