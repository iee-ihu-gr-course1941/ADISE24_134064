<?php
require_once '../../../src/config.php';
require_once '../../../src/helpers/db.php';

$db = get_db_connection();
$data = json_decode(file_get_contents('php://input'), true);

$username = $data['username'];
$password = $data['password'] ?? null;

// get user
$stmt = $db->prepare("SELECT id, password_hash FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Invalid username']);
    exit;
}

if ($user['password_hash']) {
    // validate password
    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid password']);
        exit;
    }
}

// generate auth token
$auth_token = bin2hex(random_bytes(32));
$stmt = $db->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
$stmt->execute([$auth_token, $user['id']]);

echo json_encode(['success' => true, 'auth_token' => $auth_token]);
?>
