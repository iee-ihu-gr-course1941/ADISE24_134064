<?php
require_once '../../src/config.php';
require_once '../../src/helpers/db.php';

$db = get_db_connection();

// check auth token
$headers = getallheaders();
$auth_token = $headers['Authorization'] ?? null;

if (!$auth_token) {
    die(json_encode(["error" => "No auth token provided", "headers" => $headers]));
}

// validate user
$stmt = $db->prepare("SELECT id, username FROM users WHERE auth_token = ?");
$stmt->execute([$auth_token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Invalid auth token']);
    exit;
}

// create game
$stmt = $db->prepare("INSERT INTO games (status) VALUES ('pending')");
$stmt->execute();
$game_id = $db->lastInsertId();

$color = 'blue';
$stmt = $db->prepare("INSERT INTO players (name, game_id, color, user_id) VALUES (?, ?, ?, ?)");
$stmt->execute([$user['username'], $game_id, $color, $user['id']]);
$player_id = $db->lastInsertId(); // Get the player ID for the first player

// set the turn with the 1st players id
$stmt = $db->prepare("UPDATE games SET turn = ? WHERE id = ?");
$stmt->execute([$player_id, $game_id]);

echo json_encode(['success' => true, 'game_id' => $game_id, 'message' => 'Game created and joined as first player']);
