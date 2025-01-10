<?php

class Player {
    private $id;
    private $userId;
    private $name;
    private $color;
    private $pieces;
    private $placedPositions;
    private $score;

    public function __construct(int $id, int $userId, string $name, string $color, array $pieces) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->color = $color;
        $this->pieces = $pieces;
        $this->placedPositions = [];
        $this->score = 0;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getColor(): string {
        return $this->color;
    }

    public function getPieces(): array {
        return $this->pieces;
    }

    public function removePiece(Piece $piece) {
        foreach ($this->pieces as $index => $p) {
            if ($p->getName() === $piece->getName()) {
                unset($this->pieces[$index]);
                $this->pieces = array_values($this->pieces);
                return;
            }
        }
    }

    public function addPlacedPositions(array $positions) {
        foreach ($positions as $pos) {
            $this->placedPositions[] = $pos;
        }
    }

    public function getPlacedPositions(): array {
        return $this->placedPositions;
    }

    public function setScore(int $score) {
        $this->score = $score;
    }

    public function getScore(): int {
        return $this->score;
    }
}
