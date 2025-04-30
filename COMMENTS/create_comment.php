<?php 
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

// Get the data from the POST request
$blog_id = clean_input($_POST["blog_id"] ?? null);
$author_id = clean_input($_POST["author_id"] ?? null);
$comment = clean_input($_POST["comment"] ?? null);

// Check if blog_id, author_id, and comment_text are provided
if (!$blog_id || !$author_id || !$comment) {
    echo json_encode([
        "status" => "error",
        "message" => "blog_id, author_id, and comment are required.",
    ]);
    exit;
}

// Prepare the SQL query to insert the comment
$stmt = $conn->prepare("INSERT INTO comments (blog_id, author_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $blog_id, $author_id, $comment);

// Execute the query
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Comment created successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to create comment."
    ]);
}
?>
