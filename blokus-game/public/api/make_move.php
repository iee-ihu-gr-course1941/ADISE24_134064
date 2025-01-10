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
$piece_name = $data['piece'] ?? null;
$start_row = $data['row'] ?? null;
$start_col = $data['col'] ?? null;

if (!$game_id || !$piece_name || $start_row === null || $start_col === null) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
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

// get players
$stmt = $db->prepare("SELECT * FROM players WHERE game_id = ?");
$stmt->execute([$game_id]);
$playerRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// board
$board = new Board(BOARD_SIZE);
$stmt = $db->prepare("SELECT * FROM board WHERE game_id = ?");
$stmt->execute([$game_id]);
$boardCells = $stmt->fetchAll(PDO::FETCH_ASSOC);
$boardState = $board->getBoardState();
foreach ($boardCells as $cell) {
    $boardState[$cell['row']][$cell['col']] = $cell['color'];
}

$reflection = new ReflectionClass($board);
$gridProperty = $reflection->getProperty('grid');
$gridProperty->setAccessible(true);
$gridProperty->setValue($board, $boardState);

// create player object and default pieces
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

// players turn
$turnPlayerId = $gameRow['turn'];
$currentPlayer = null;
foreach ($players as $pl) {
    if ($pl->getId() == $turnPlayerId) {
        $currentPlayer = $pl;
        break;
    }
}

if (!$currentPlayer) {
    echo json_encode(['success' => false, 'error' => 'No current player found for this game']);
    exit;
}

// check user_id matches the logged-in user
if ($currentPlayer->getUserId() != $user['id']) {
    echo json_encode(['success' => false, 'error' => 'Not your turn. You are not the current player.']);
    exit;
}

$gameObj = new Game($game_id, $players, $board);

$playerColor = $currentPlayer->getColor();

// make move
$success = $gameObj->makeMove($piece_name, $start_row, $start_col, $playerColor);
if (!$success) {
    echo json_encode(['success' => false, 'error' => 'Invalid move']);
    exit;
}

// update board
$newBoardState = $board->getBoardState();
$stmt = $db->prepare("DELETE FROM board WHERE game_id = ?");
$stmt->execute([$game_id]);

for ($r=0; $r<BOARD_SIZE; $r++) {
    for ($c=0; $c<BOARD_SIZE; $c++) {
        if ($newBoardState[$r][$c] !== null) {
            $stmt = $db->prepare("INSERT INTO board (game_id, row, col, color) VALUES (?, ?, ?, ?)");
            $stmt->execute([$game_id, $r, $c, $newBoardState[$r][$c]]);
        }
    }
}

// update turn
//$nextPlayer = $gameObj->getCurrentPlayer();
//$stmt = $db->prepare("UPDATE games SET turn = ? WHERE id = ?");
//$stmt->execute([$nextPlayer->getId(), $game_id]);

// swap turn
$nextPlayerId = ($playerRows[0]['id'] == $turnPlayerId) ? $playerRows[1]['id'] : $playerRows[0]['id'];
$stmt = $db->prepare("UPDATE games SET turn = ? WHERE id = ?");
$stmt->execute([$nextPlayerId, $game_id]);

echo json_encode(['success' => true, 'message' => 'Move made successfully']);