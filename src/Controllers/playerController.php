<?php 

namespace src\Controllers;

trait Player{
    public function getPlayerName($player) {
        return $player == 0 ? 'White' : 'Black';
    }

    public function getActivePlayer() {
        return $this->activePlayer;
    }

    public function getHand($player) {
        return array_filter($this->hand[$player], function ($ct) { return $ct > 0; });
    }

    public function getHandHtml($player) {
        $html = "";
        foreach ($this->hand[$player] as $tile => $ct) {
            for ($i = 0; $i < $ct; $i++) {
                $html .= '<div class="tile player'.$player.'"><span>'.$tile."</span></div> ";
            }
        }
        return $html;    
    }
}