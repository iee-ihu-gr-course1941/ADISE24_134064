<?php

require_once __DIR__ . '/Board.php';
require_once __DIR__ . '/Player.php';
require_once __DIR__ . '/Piece.php';

class Game {
    private $id;
    private $board;
    private $players;
    private $status;
    private $turnIndex;

    public function __construct(int $id, array $players, Board $board) {
        $this->id = $id;
        $this->board = $board;
        $this->players = $players;
        $this->status = 'active';
        $this->turnIndex = 0; // Start with the first player in the array
    }

    public function getId(): int {
        return $this->id;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getCurrentPlayer(): Player {
        return $this->players[$this->turnIndex];
    }

    public function nextTurn() {
        $this->turnIndex = ($this->turnIndex + 1) % count($this->players);
    }

    // place a piece on the board for the current player
    public function makeMove(string $pieceName, int $startRow, int $startCol, string $color): bool {
        $player = $this->getCurrentPlayer();
        $piece = $this->findPiece($player, $pieceName);

        if (!$piece) {
            return false; // player does not have this piece
        }

        // validate move
        //$color = $player->getColor();
        $playerPositions = $player->getPlacedPositions();
        if (!$this->board->canPlacePiece($piece, $startRow, $startCol, $color, $playerPositions)) {
            return false;
        }

        // place the piece
        $this->board->placePiece($piece, $startRow, $startCol, $color);

        // update player's placed positions
        $offsets = $piece->getCoordinates();
        $newPositions = [];
        foreach ($offsets as [$r, $c]) {
            $newPositions[] = [$startRow + $r, $startCol + $c];
        }
        $player->addPlacedPositions($newPositions);

        // remove piece from player's inventory
        $player->removePiece($piece);

        // move to next turn
        $this->nextTurn();
        return true;
    }

    private function findPiece(Player $player, string $pieceName): ?Piece {
        foreach ($player->getPieces() as $p) {
            if ($p->getName() === $pieceName) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Check if the game is over.
     * This could check if all players cannot move or no pieces remain.
     */
    public function checkGameOver(): bool {
        // A simple placeholder: Game ends if all players have no pieces left.
        foreach ($this->players as $p) {
            if (count($p->getPieces()) > 0) {
                return false;
            }
        }
        $this->status = 'finished';
        return true;
    }

    public function calculateScores() {
        // Example scoring: -1 per unused square in player's remaining pieces
        foreach ($this->players as $player) {
            $score = 0;
            foreach ($player->getPieces() as $piece) {
                $score -= count($piece->getCoordinates());
            }
            $player->setScore($score);
        }
    }

    public function getPlayers(): array {
        return $this->players;
    }

    public function getBoardState(): array {
        return $this->board->getBoardState();
    }
}
