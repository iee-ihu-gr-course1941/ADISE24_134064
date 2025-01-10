<?php
require_once '../../../src/config.php';
require_once '../../../src/helpers/db.php';

$db = get_db_connection();
$auth_token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$auth_token) {
    echo json_encode(['success' => false, 'error' => 'No auth token provided']);
    exit;
}

// invalidate token
$stmt = $db->prepare("UPDATE users SET auth_token = NULL WHERE auth_token = ?");
$stmt->execute([$auth_token]);

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>
