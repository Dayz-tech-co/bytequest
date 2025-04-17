<?php
include "../CONFIG/bytequest_db.php";

header("Content-Type: application/json");

$stmt=$conn->prepare("SELECT * FROM comments ORDER BY comment_id DESC");
$stmt->execute();
$result=$stmt->get_result();

$comments= [];
while($row=$result->fetch_assoc()){
    $comments[]=$row;

}
echo json_encode([
    "status" => "success",
    "message" => "All comments fetched successfully.",
    "data" => $comments
]);
?>