<?php 
require_once "../CONFIG/bootstrap.php";

header("Content-Type: application/json");

$comment_id = $_POST["comment_id"] ?? null;
$comment = $_POST["comment"] ?? null;

if(!$comment_id || !$comment) {
    echo json_encode([
        "status" => "false",
        "message" => "All Fields (comment id and comment text) Are Required."
    ]);
    exit;
}

$stmt=$conn->prepare("UPDATE comments SET comment = ? WHERE comment_id = ? ");
$stmt->bind_param("si", $comment, $comment_id);

if ($stmt->execute()){
    echo json_encode([
        "status" => "true",
        "message" => "Comment updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "false",
        "message" => "Failed to update comment."
    ]);
}
?>