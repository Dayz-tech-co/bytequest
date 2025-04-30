<?php

include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

$title = clean_input( $_POST["title"] ?? null);
$content = clean_input($_POST["content"] ?? null);
$image = clean_input($_POST["image"] ?? null);//  This can be optional shaa
$author_id = clean_input($_POST["author_id"] ?? null);
// Check if the required data is being provided
if (!$title || !$content || !$author_id){
    echo json_encode(["status" => "error", "message" => "All Fields are required."]);
    exit;
}
// Prepare the SQL query to insert the blog post
$stmt=$conn->prepare("INSERT INTO blogs (title, content, image, author_id) VALUES (?,?,?,?)");
$stmt->bind_param("sssi", $title, $content, $image, $author_id);

// Execute the query

if ($stmt->execute()){
    echo json_encode(["status" => "success", "message" => "Blog post created successfully."]);
} else {
    echo json_encode(["status" => "error", "mesaage" => "Failed to create blog post."]);
}
?>