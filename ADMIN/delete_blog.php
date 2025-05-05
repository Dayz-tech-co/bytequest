<?php 
include "./CONFIG/bootstrap.php";

header( "Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST"){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method."
    ]);
    exit;
}
function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$blog_id = clean_input( $_POST["blog_id"]);
$admin_id = $decoded["admin_id"];

if (!$blog_id || !$admin_id){
    echo json_encode([
        "status" => "false",
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
    "status" => "false",
    "message" => "Unauthorised: admin not found",
 ]);
 exit;

}

// Delete the blog

$delete_stmt=$conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
$delete_stmt->bind_param("i", $blog_id);

if ($delete_stmt->execute()){
    echo json_encode([
        "status" => "true",
        "message" => "Blog Deleted Successfully.",
    ]);
} else {
    echo json_encode([
        "status" => "false",
        "message" => "Failed To Delete Blog. "
    ]);
}
?>