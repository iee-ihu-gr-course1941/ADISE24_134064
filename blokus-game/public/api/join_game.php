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

$data = json_decode(file_get_contents('php://input'), true);
$game_id = $data['game_id'] ?? null;

if (!$game_id) {
    echo json_encode(['success' => false, 'error' => 'No game_id provided']);
    exit;
}

// check if game exists
$stmt = $db->prepare("SELECT id, status FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$game) {
    echo json_encode(['success' => false, 'error' => 'Game not found']);
    exit;
}

if ($game['status'] !== 'pending') {
    echo json_encode(['success' => false, 'error' => 'Game not in pending state']);
    exit;
}

// check how many players are currently in the game
$stmt = $db->prepare("SELECT COUNT(*) as count FROM players WHERE game_id = ?");
$stmt->execute([$game_id]);
$count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

if ($count >= 4) {
    echo json_encode(['success' => false, 'error' => 'Game is full (max 4 players)']);
    exit;
}

// check if user is already in the game
$stmt = $db->prepare("SELECT id FROM players WHERE game_id = ? AND user_id = ?");
$stmt->execute([$game_id, $user['id']]);
$existingPlayer = $stmt->fetch(PDO::FETCH_ASSOC);
if ($existingPlayer) {
    echo json_encode(['success' => false, 'error' => 'User already joined this game']);
    exit;
}

// assign a color based on how many players are present
$colors = ['blue', 'yellow', 'red', 'green'];
$color = $colors[$count];

// insert player record associated with this user
$stmt = $db->prepare("INSERT INTO players (name, game_id, color, user_id) VALUES (?, ?, ?, ?)");
$stmt->execute([$user['username'], $game_id, $color, $user['id']]);

echo json_encode(['success' => true, 'message' => 'Joined game successfully', 'color' => $color]);
