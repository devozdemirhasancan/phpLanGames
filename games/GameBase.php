<?php
// games/GameBase.php
abstract class GameBase {
    protected $room;
    protected $player;
    protected $players;
    protected $rounds;
    protected $currentRound;
    protected $gameFile;
    protected $db;

    public function __construct($db, $room, $player, $players, $rounds, $currentRound, $gameFile) {
        $this->db = $db;
        $this->room = $room;
        $this->player = $player;
        $this->players = $players;
        $this->rounds = $rounds;
        $this->currentRound = $currentRound;
        $this->gameFile = $gameFile;
    }
    // Oyun ekranını gösterir
    abstract public function render();
    // Cevap gönderme işlemi
    abstract public function handlePost();
    // Tüm oyuncular bitirdi mi?
    abstract public function isFinished();
}
