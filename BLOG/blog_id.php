<?php
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

// Get blog_id from the query parameter
$blog_id = clean_input( $_GET['blog_id'] ?? null);

if (!$blog_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Blog ID is required."
    ]);
    exit;
}

// Prepare SQL query to fetch blog
$stmt = $conn->prepare("SELECT blog_id, title, content, image, author_id FROM blogs WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);

// Execute query
if ($stmt->execute()) {
    // Fetch result
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $blog = $result->fetch_assoc();
        echo json_encode([
            "status" => "success",
            "message" => "Blog fetched successfully.",
            "data" => $blog
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No blog found with the provided ID."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch blog post."
    ]);
}

$stmt->close();
?>
