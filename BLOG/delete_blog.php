<?php
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

// Get blog_id from the request
$blog_id = clean_input($_GET['blog_id'] ?? null);

// Check if blog_id is provided
if (!$blog_id) {
    echo json_encode([
        "status" => "error",
        "message" => "blog_id is required."
    ]);
    exit;
}

// Prepare SQL query to delete the blog
$stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);  // Bind the blog_id as integer

// Execute the query
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "status" => "success",
            "message" => "Blog deleted successfully."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Blog not found or already deleted."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete blog."
    ]);
}

$stmt->close();
?>
