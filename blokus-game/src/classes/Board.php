<?php

class Board {
    private $size;
    private $grid;

    // create 20x20 board
    public function __construct(int $size = 20) {
        $this->size = $size;
        $this->resetBoard();
    }

    // reset board
    public function resetBoard() {
        $this->grid = array_fill(0, $this->size, array_fill(0, $this->size, null));
    }

    // board piece validation
    public function canPlacePiece(Piece $piece, int $startRow, int $startCol, string $color, array $playerPositions): bool {
        $coords = $piece->getCoordinates();
        foreach ($coords as [$r, $c]) {
            $boardR = $startRow + $r;
            $boardC = $startCol + $c;

            // bounds
            if ($boardR < 0 || $boardR >= $this->size || $boardC < 0 || $boardC >= $this->size) {
                return false;
            }

            // piece already exists
            if ($this->grid[$boardR][$boardC] !== null) {
                return false;
            }
        }

        // validation for pieces corners
        $cornerContact = false;

        foreach ($coords as [$r, $c]) {
            $boardR = $startRow + $r;
            $boardC = $startCol + $c;

            // check all 8 directions around the placed square
            $directions = [
                [-1, 0], [1, 0], [0, -1], [0, 1],
                [-1, -1], [-1, 1], [1, -1], [1, 1]
            ];

            foreach ($directions as [$dr, $dc]) {
                $nr = $boardR + $dr;
                $nc = $boardC + $dc;
                if ($nr >= 0 && $nr < $this->size && $nc >= 0 && $nc < $this->size) {
                    $cellVal = $this->grid[$nr][$nc];
                    if ($cellVal === $color) {
                        if (($dr == -1 && $dc == 0) || ($dr == 1 && $dc == 0) || ($dr == 0 && $dc == -1) || ($dr == 0 && $dc == 1)) {
                            // only first piece can touch the edges
                            if (count($playerPositions) > 0) {
                                return false;
                            }
                        } else {
                            // touches the corner of the piece
                            $cornerContact = true;
                        }
                    }
                }
            }
        }

        // If this is not the first piece, we must have corner contact
        if (count($playerPositions) > 0 && !$cornerContact) {
            return false;
        }

        return true;
    }

    // place the piece on the board
    public function placePiece(Piece $piece, int $startRow, int $startCol, string $color): bool {
        $coords = $piece->getCoordinates();
        foreach ($coords as [$r, $c]) {
            $this->grid[$startRow + $r][$startCol + $c] = $color;
        }
        return true;
    }

    // board state
    public function getBoardState(): array {
        return $this->grid;
    }

    public function getSize(): int {
        return $this->size;
    }
}
