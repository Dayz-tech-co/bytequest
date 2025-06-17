<?php
require_once "../config/bootstrap.php";

header("Content-Type: application/json");

// Get Authorization header
$headers = apache_request_headers();

$authHeader = $headers['Authorization'];
$token = str_replace("Bearer ", "", $authHeader);

if (empty ($token)){
    response(false, "Authorization Token Missing");
}

$decoded = decode_jwt($token);
if (!$decoded || !isset($decoded['id'])) {
    response(false, "Invalid or expired token.");
}

// 🔐 Get user ID from token
$author_id = $decoded['id'];

$title = clean_input($_POST["title"] ?? null);
$content = clean_input($_POST["content"] ?? null);
$image = clean_input($_POST["image"] ?? null); // Optional field

// Check if the required data is provided
if (!$title || !$content) {
  response(false, "Title and content are required.");
}

// Check if blog with the same title already exists for this author

$check_stmt=$conn->prepare("SELECT id FROM blogs WHERE title =? AND author_id = ? ");
$check_stmt->bind_param("si", $title, $author_id);
$check_stmt->execute();
$check_stmt->store_result();
if ($check_stmt->num_rows()>0){
    response(false, "Blog already added by this user");
}
$check_stmt->close();
// Prepare the SQL query to insert the blog post
$stmt = $conn->prepare("INSERT INTO blogs (title, content, image, author_id) VALUES (?, ?, ?, ?)");
if ($stmt === false) {
    response(false, "Failed to prepare the SQL statement.");
}

// If image is optional, you can pass NULL if it's not provided
if (empty($image)) {
    $image = NULL;
}

// Bind the parameters and execute the query
$stmt->bind_param("sssi", $title, $content, $image, $author_id);

if ($stmt->execute()) {
    response(true, "Blog post created successfully.");
} else {
    response(false, "Failed to create blog post.");
}

$stmt->close(); // Close the statement
?>
