<?php
require_once '../../../src/config.php';
require_once '../../../src/helpers/db.php';

$db = get_db_connection();
$auth_token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$auth_token) {
    echo json_encode(['success' => false, 'error' => 'No auth token provided']);
    exit;
}

// validate token
$stmt = $db->prepare("SELECT id FROM users WHERE auth_token = ?");
$stmt->execute([$auth_token]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Invalid auth token']);
    exit;
}

echo json_encode(['success' => true, 'user_id' => $user['id']]);
?>
