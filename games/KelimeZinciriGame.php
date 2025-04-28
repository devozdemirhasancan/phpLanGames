<?php
require_once __DIR__ . '/GameBase.php';
class KelimeZinciriGame extends GameBase {
    public function handlePost() {
        $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
        $turn = isset($gameData['turn']) ? (int)$gameData['turn'] : 0;
        $currentPlayer = $this->players[$turn % count($this->players)];
        $zincir = isset($gameData['zincir']) ? $gameData['zincir'] : [];
        $maxMoves = $this->rounds * count($this->players);
        // Zincirdeki kelimeler sadece kelime olarak tutulacak, kullanıcıyla birlikte tutulacak
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kelime']) && $this->player === $currentPlayer && $turn < $maxMoves
        ) {
            $cevap = trim($_POST['kelime']);
            $cevapLower = mb_strtolower($cevap);
            // Zincirdeki kelimelerden sadece sonuncusu hariç tümünü kontrol et
            $kullanilmisKelimeler = array_map(function($item) {
                return mb_strtolower(is_array($item) ? $item['kelime'] : $item);
            }, $zincir);
            $sonKelimeIndex = count($kullanilmisKelimeler) - 1;
            // Eğer zincir boşsa veya ilk hamle ise, tekrar kontrolü yapma
            if (!empty($zincir)) {
                $cevapIndices = array_keys($kullanilmisKelimeler, $cevapLower, true);
                // Eğer zincirde girilen kelime sadece son kelime olarak varsa hata verme, başka yerde de varsa hata ver
                if (count($cevapIndices) > 0) {
                    // Sadece son kelimeyle aynıysa ve başka yerde yoksa hata verme
                    if (!(count($cevapIndices) === 1 && $cevapIndices[0] === $sonKelimeIndex)) {
                        $_SESSION['kelime_hata'] = 'Bu kelime daha önce kullanıldı!';
                        return;
                    }
                }
            }
            // İlk kelime için harf kontrolü, diğerlerinde son harf kontrolü
            if (empty($zincir)) {
                $beklenen = mb_strtolower(trim($gameData['ilk_harf']));
                $ilkHarf = mb_strtolower(mb_substr($cevapLower, 0, 1));
                if ($ilkHarf !== $beklenen) {
                    $_SESSION['kelime_hata'] = 'Kelime "' . mb_strtoupper($beklenen) . '" harfiyle başlamalı!';
                    return;
                }
            } else {
                $sonKelimeObj = end($zincir);
                $sonKelimeStr = is_array($sonKelimeObj) ? $sonKelimeObj['kelime'] : $sonKelimeObj;
                $sonKelimeStr = trim($sonKelimeStr);
                $beklenen = mb_strtolower(mb_substr($sonKelimeStr, -1));
                $ilkHarf = mb_strtolower(mb_substr($cevapLower, 0, 1));
                if ($ilkHarf !== $beklenen) {
                    $_SESSION['kelime_hata'] = 'Kelime "' . mb_strtoupper($beklenen) . '" harfiyle başlamalı!';
                    return;
                }
            }
            $gameData['answers'][$this->player . '_' . $turn] = $cevap;
            $gameData['zincir'][] = [
                'player' => $this->player,
                'kelime' => $cevap
            ];
            $gameData['turn'] = $turn + 1;
            file_put_contents($this->gameFile, json_encode($gameData));
            unset($_SESSION['kelime_hata']);
        }
        // Süre dolarsa otomatik olarak zincirin son kelimesi tekrar eklensin ve sıra geçsin
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['auto_pass']) && $this->player === $currentPlayer && $turn < $maxMoves
        ) {
            $sonKelimeArr = end($zincir);
            $sonKelime = is_array($sonKelimeArr) ? $sonKelimeArr['kelime'] : $sonKelimeArr;
            $gameData['answers'][$this->player . '_' . $turn] = $sonKelime;
            $gameData['zincir'][] = [
                'player' => $this->player,
                'kelime' => $sonKelime
            ];
            $gameData['turn'] = $turn + 1;
            file_put_contents($this->gameFile, json_encode($gameData));
            unset($_SESSION['kelime_hata']);
        }
        // Oyun bitti mi kontrolü: Eğer tur son hareketse, sonuçlara yönlendir
        $turn = isset($gameData['turn']) ? (int)$gameData['turn'] : 0;
        $maxMoves = $this->rounds * count($this->players);
        if ($turn >= $maxMoves) {
            echo '<div class="alert alert-info text-center my-5">Oyun bitti, sonuçlar yükleniyor...</div>';
            echo '<script>setTimeout(function(){ window.location.href = "results.php?room=' . urlencode($this->room) . '&player=' . urlencode($this->player) . '"; }, 1000);</script>';
            exit;
        }
    }
    public function isFinished() {
        $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
        $turn = isset($gameData['turn']) ? (int)$gameData['turn'] : 0;
        $maxMoves = $this->rounds * count($this->players);
        return $turn >= $maxMoves;
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
    public function render() {
        $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
        if (!isset($gameData['zincir']) || !is_array($gameData['zincir'])) {
            $letters = array('A','B','C','Ç','D','E','F','G','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z');
            $ilkHarf = $letters[array_rand($letters)];
            $gameData['ilk_harf'] = $ilkHarf;
            $gameData['zincir'] = [];
            $gameData['turn'] = 0;
            $gameData['answers'] = [];
            file_put_contents($this->gameFile, json_encode($gameData));
        }
        $zincir = isset($gameData['zincir']) ? $gameData['zincir'] : [];
        $turn = isset($gameData['turn']) ? (int)$gameData['turn'] : 0;
        $currentPlayer = $this->players[$turn % count($this->players)];
        $isMyTurn = ($this->player === $currentPlayer);
        $maxMoves = $this->rounds * count($this->players);
        $oyunBittiMi = ($turn >= $maxMoves);
        // Oyun bittiyse sonuçlara yönlendir, zincir güncelken
        if ($oyunBittiMi) {
            echo '<div class="alert alert-info text-center my-5">Oyun bitti, sonuçlar yükleniyor...</div>';
            echo '<script>setTimeout(function(){ window.location.href = "results.php?room=' . urlencode($this->room) . '&player=' . urlencode($this->player) . '"; }, 1000);</script>';
            return;
        }
        if (empty($zincir)) {
            $sonKelime = $gameData['ilk_harf'];
            $ilkKelimeMi = true;
        } else {
            $sonKelime = is_array(end($zincir)) ? end($zincir)['kelime'] : end($zincir);
            $ilkKelimeMi = false;
        }
        $answers = isset($gameData['answers']) ? $gameData['answers'] : [];
        $kullanilmisKelimeler = array_map(function($item) {
            return mb_strtolower(is_array($item) ? $item['kelime'] : $item);
        }, $zincir);
        $sure = 10;
        include __DIR__ . '/../templates/kelimezinciri_form.php';
    }
}
