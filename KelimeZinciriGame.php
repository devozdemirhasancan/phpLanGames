<?php
require_once __DIR__ . '/GameBase.php';
class KelimeZinciriGame extends GameBase {
    public function handlePost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kelime'])) {
            $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
            $cevap = trim($_POST['kelime']);
            $gameData['answers'][$this->player] = $cevap;
            $gameData['zincir'][] = $cevap;
            file_put_contents($this->gameFile, json_encode($gameData));
        }
    }
    public function isFinished() {
        $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
        return isset($gameData['answers']) && count($gameData['answers']) === count($this->players);
    }
    public function render() {
        $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
        // Eğer zincir yoksa ilk harfi belirle ve zinciri başlat
        if (!isset($gameData['zincir']) || !is_array($gameData['zincir']) || count($gameData['zincir']) === 0) {
            // Türkçede başta kullanılabilen harfler (Ğ, Q, W, X hariç)
            $letters = array('A','B','C','Ç','D','E','F','G','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z');
            $ilkHarf = $letters[array_rand($letters)];
            $gameData['ilk_harf'] = $ilkHarf;
            $gameData['zincir'] = [];
            file_put_contents($this->gameFile, json_encode($gameData));
        }
        $zincir = isset($gameData['zincir']) && is_array($gameData['zincir']) ? $gameData['zincir'] : [];
        // Zincir boşsa ilk oyuncu için ilk harf ile başlatma
        if (empty($zincir)) {
            $sonKelime = $gameData['ilk_harf'];
            $ilkKelimeMi = true;
        } else {
            $sonKelime = end($zincir);
            $ilkKelimeMi = false;
        }
        $answers = isset($gameData['answers']) ? $gameData['answers'] : [];
        include __DIR__ . '/../templates/kelimezinciri_form.php';
    }
    // Puanlama fonksiyonu ve zincir döndürme
    public static function puanla($room, $round, $players) {
        $gameFile = __DIR__ . '/../data/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $room) . '_game_' . $round . '.json';
        $puanlar = array_fill_keys($players, 0);
        $kelimeler = [];
        if (file_exists($gameFile)) {
            $gameData = json_decode(file_get_contents($gameFile), true);
            $kelimeler = isset($gameData['answers']) ? $gameData['answers'] : [];
        }
        // Aynı kelimeyi yazanlar düşük puan alır
        $frekans = array_count_values(array_map('mb_strtolower', $kelimeler));
        foreach ($players as $p) {
            $cevap = isset($kelimeler[$p]) ? mb_strtolower($kelimeler[$p]) : '';
            if ($cevap === '') continue;
            if ($frekans[$cevap] === 1) {
                $puanlar[$p] += 10;
            } else {
                $puanlar[$p] += 3;
            }
        }
        return [$puanlar, $kelimeler];
    }
}
