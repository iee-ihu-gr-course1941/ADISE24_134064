<?php
require_once '../../../src/config.php';
require_once '../../../src/helpers/db.php';

$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'];
$password = $data['password'] ?? null;

// check if user exists
$stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Username already exists']);
    exit;
}

// insert user
if ($password) {
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
} else {
    $password_hash = null; // login without password
}

$stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
$stmt->execute([$username, $password_hash]);

echo json_encode(['success' => true, 'message' => 'User registered successfully']);
?>
