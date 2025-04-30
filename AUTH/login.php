<?php
require_once '../CONFIG/bytequest_db.php';
require_once '../CONFIG/jwt_helper.php';

header("Content-Type: application/json");

function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$data = json_decode(file_get_contents("php://input"), true);
$email = clean_input($_POST['email']);
$password = trim($_POST['password']); // password: trim only, no htmlspecialchars


if (!$email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
    exit;
}

$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $token = generate_jwt($user['id'], $user['role']);
    echo json_encode(['status' => 'success', 'token' => $token]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
}
?>
