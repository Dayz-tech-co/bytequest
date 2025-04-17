<?php 
include "../CONFIG/bytequest_db.php";

header( "Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST"){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method."
    ]);
    exit;
}

$blog_id = $_POST["blog_id"] ?? null;
$admin_id = $_POST["admin_id"] ?? null;

if (!$blog_id || !$admin_id){
    echo json_encode([
        "status" => "error",
        "message" => "Both fields (blog id and admin id) are required.",
    ]);
    exit;
}
// Confirm if admin exists
$admin_stmt=$conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result=$admin_stmt->get_result();

if ($admin_result->num_rows===0){
 echo json_encode([
    "status" => "error",
    "message" => "Unauthorised: admin not found",
 ]);
 exit;

}

// Delete the blog

$delete_stmt=$conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
$delete_stmt->bind_param("i", $blog_id);

if ($delete_stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "Blog Deleted Successfully.",
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed To Delete Blog. "
    ]);
}
?>