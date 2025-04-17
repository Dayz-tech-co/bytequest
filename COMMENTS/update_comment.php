<?php 
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

$comment_id = $_POST["comment_id"] ?? null;
$comment = $_POST["comment"] ?? null;

if(!$comment_id || !$comment) {
    echo json_encode([
        "status" => "error",
        "message" => "All Fields (comment id and comment text) Are Required."
    ]);
    exit;
}

$stmt=$conn->prepare("UPDATE comments SET comment = ? WHERE comment_id = ? ");
$stmt->bind_param("si", $comment, $comment_id);

if ($stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "Comment updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update comment."
    ]);
}
?>