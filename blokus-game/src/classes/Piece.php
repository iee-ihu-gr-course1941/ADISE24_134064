<?php

class Piece {
    private $name;
    private $coordinates; // piece shape

    public function __construct(string $name, array $coordinates) {
        $this->name = $name;
        $this->coordinates = $coordinates;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getCoordinates(): array {
        return $this->coordinates;
    }
}
