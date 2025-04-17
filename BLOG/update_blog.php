<?php 
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

// Get the data from the POST request

$blog_id = $_POST["blog_id"] ?? null;
$title = $_POST["title"] ?? null;
$content = $_POST["content"] ?? null;
$image = $_POST["image"] ?? null;
// Check if blog_id, title, and content are provided

if (!$blog_id || !$title || !$content){
    echo json_encode([
        "status" => "error",
        "message" => "blog_id, title and content are required.",
    ]);
    exit;
}

$image = $_POST["image"] ?? null;  // Ensure $image is null if not provided

// Handle the case where image is empty
if ($image === "") {
    $image = null;
}

// Prepare the SQL query
$stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, image = ? WHERE blog_id = ?");

// Check if the $image is null and bind the parameters accordingly
if ($image === null) {
    $stmt->bind_param("sssi", $title, $content, $image, $blog_id);
} else {
    $stmt->bind_param("sssi", $title, $content, $image, $blog_id);
}

// Execute the query
if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Blog post updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update the blog post"
    ]);
}

?>