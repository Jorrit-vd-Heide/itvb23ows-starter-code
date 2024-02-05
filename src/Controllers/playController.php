<?php

namespace src\Controllers;

include_once 'views/util.php';
include_once 'playerController.php';
include_once 'gameStateController.php';


trait PlayTile {
    public function playTile($piece, $to) {
        $this->clearError();

        $hand = $this->getHand($this->getActivePlayer());

        if (!$hand[$piece]) {
            $this->setError("Player does not have tile");
        } elseif (isset($this->board[$to])) {
            $this->setError('Board position is not empty');
        } elseif (count($this->board) && !$this->hasNeighbour($to, $this->board)) {
            $this->setError("Board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !$this->neighboursAreSameColor($this->getActivePlayer(), $to, $this->board)) {
            $this->setError("Board position has opposing neighbour");
        } elseif (array_sum($hand) <= 8 && $hand['Q']) {
            $this->setError("Must play queen bee");
        }
        if ($this->hasError()) return;

        $this->board[$to] = [[$this->getActivePlayer(), $piece]];
        $this->hand[$this->getActivePlayer()][$piece]--;
        $this->current_player = $this->getActivePlayer() == 0 ? 1 : 0;
        $stmt = $this->database->prepare('insert into moves (game_id, type, move_from, move_to, previous_id, state) values (?, ?, "play", ?, ?, ?)');
        $stmt->bind_param('issis', $this->game_id, $piece, $to, $this->last_move, $this->setState());
        $stmt->execute();
        $this->last_move = $this->database->insert_id;
    }
}
