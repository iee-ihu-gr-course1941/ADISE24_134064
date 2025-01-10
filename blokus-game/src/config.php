<?php
// database configuration
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1:3306');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'blokus_game');

// other constants
define('BOARD_SIZE', 20); // 20x20 board
?>
