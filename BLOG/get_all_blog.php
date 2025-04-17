<?php
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

// Prepare SQL query to fetch all blogs
$stmt = $conn->prepare("SELECT blog_id, title, content, image, author_id FROM blogs");

// Execute query
if ($stmt->execute()) {
    // Fetch results
    $result = $stmt->get_result();
    $blogs = [];

    if ($result->num_rows > 0) {
        // Loop through results and store in an array
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }

        // Respond with all the blogs
        echo json_encode([
            "status" => "success",
            "message" => "All blogs fetched successfully.",
            "data" => $blogs
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No blogs found."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch blogs."
    ]);
}

$stmt->close();
?>
