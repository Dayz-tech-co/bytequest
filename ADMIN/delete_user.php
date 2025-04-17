<?php 
include "../CONFIG/bytequest_db.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !=="POST"){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Request Method.",
    ]);
    exit;
}
$data = $_POST;
$admin_id = $_POST["admin_id"] ?? null;
$id = $_POST["id"] ?? null;

if (!$admin_id || !$id){
    echo json_encode([
        "status"  => "error",
        "message" => "Both fields (admin id and user id) Are Required",
    ]);
    exit;
}
// Verify admin exists

$admin_stmt=$conn->prepare("SELECT * FROM admins WHERE admin_id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_result=$admin_stmt->get_result();

if ($admin_result->num_rows===0){
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorised: Admin not found."
    ]);
    exit;
}

// Delete user
$delete_stmt=$conn->prepare("DELETE FROM users WHERE id = ? ");
$delete_stmt->bind_param("i", $id);
if ($delete_stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "User Deleted Successfully."
    ]);
} else {
    echo json_encode([
        "status"=> "error",
        "message" => "Failed to Delete User"
    ]);
}
?>