<?php 
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

$comment_id = $_GET["comment_id"] ?? null;

if(!$comment_id){
    echo json_encode([
        "status" => "error",
        "message" => "comment id is required."
    ]);
    exit;
}

$stmt=$conn->prepare("SELECT * FROM comments WHERE comment_id = ? ");
$stmt->bind_param("i",$comment_id);
$stmt->execute();
$result=$stmt->get_result();

if($result->num_rows>0){
    $comment=$result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "message" => "Comment Fetched Successfully.",
        $data=$comment
    ]);

}else {
    echo json_encode([
        "status" => "error",
        "message" => "Comment not Found."
    ]);
}
?>