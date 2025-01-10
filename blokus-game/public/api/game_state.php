<?php
require_once '../../src/config.php';
require_once '../../src/helpers/db.php';
require_once '../../src/classes/Game.php';
require_once '../../src/classes/Board.php';
require_once '../../src/classes/Player.php';
require_once '../../src/classes/Piece.php';

$db = get_db_connection();

// check auth token
$headers = getallheaders();
$auth_token = $headers['Authorization'] ?? null;

if (!$auth_token) {
    die(json_encode(["error" => "No auth token provided", "headers" => $headers]));
}

$stmt = $db->prepare("SELECT id, username FROM users WHERE auth_token = ?");
$stmt->execute([$auth_token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Invalid auth token']);
    exit;
}

$game_id = $_GET['game_id'] ?? null;
if (!$game_id) {
    echo json_encode(['success' => false, 'error' => 'No game_id provided']);
    exit;
}

// load game
$stmt = $db->prepare("SELECT * FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$gameRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$gameRow) {
    echo json_encode(['success' => false, 'error' => 'Game not found']);
    exit;
}

// load players
$stmt = $db->prepare("SELECT * FROM players WHERE game_id = ?");
$stmt->execute([$game_id]);
$playerRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// load board
$board = new Board(BOARD_SIZE);
$stmt = $db->prepare("SELECT * FROM board WHERE game_id = ?");
$stmt->execute([$game_id]);
$boardCells = $stmt->fetchAll(PDO::FETCH_ASSOC);

$boardState = $board->getBoardState();
foreach ($boardCells as $cell) {
    $boardState[$cell['row']][$cell['col']] = $cell['color'];
}

// custom pieces
$players = [];
foreach ($playerRows as $pRow) {
    $pieces = [
        new Piece('square', [[0,0],[1,0],[0,1],[1,1]]),
        new Piece('lineHorizontal', [[0,0],[1,0],[2,0],[3,0],[4,0]]),
        new Piece('lineVertical', [[0,0],[0,1],[0,2],[0,3],[0,4]]),
    ];
    $player = new Player($pRow['id'], $pRow['user_id'], $pRow['name'], $pRow['color'], $pieces);

    $players[] = $player;
}

$gameObj = new Game($game_id, $players, $board);

$response = [
    'success' => true,
    'status' => $gameObj->getStatus(),
    'board' => $boardState,
    'players' => array_map(function($p) {
        return [
            'id' => $p->getId(),
            'name' => $p->getName(),
            'color' => $p->getColor(),
            'score' => $p->getScore(),
            'remaining_pieces' => array_map(fn($piece) => $piece->getName(), $p->getPieces())
        ];
    }, $gameObj->getPlayers()),
    'current_player_id' => $gameObj->getCurrentPlayer()->getId()
];

echo json_encode($response);
