<?php
require_once __DIR__ . '/GameBase.php';
class IsimSehirGame extends GameBase {
    public function handlePost() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isim'])) {
            $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
            $answer = [
                'player' => $this->player,
                'isim' => $_POST['isim'],
                'sehir' => $_POST['sehir'],
                'hayvan' => $_POST['hayvan'],
                'bitki' => $_POST['bitki'],
                'nesne' => $_POST['nesne'] ?? '',
                'unlu' => $_POST['unlu'] ?? '',
                'marka' => $_POST['marka'] ?? '',
                'renk' => $_POST['renk'] ?? '',
                'yemek' => $_POST['yemek'] ?? ''
            ];
            $gameData['answers'][$this->player] = $answer;
            file_put_contents($this->gameFile, json_encode($gameData));
        }
    }
    public function isFinished() {
        $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
        return isset($gameData['answers']) && count($gameData['answers']) === count($this->players);
    }
    public function render() {
        // Kullanılmış harfleri önceki turlardan topla
        $usedLetters = [];
        for ($i = 1; $i <= $this->rounds; $i++) {
            $oldGameFile = __DIR__ . '/../data/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $this->room) . '_game_' . $i . '.json';
            if (file_exists($oldGameFile)) {
                $oldData = json_decode(file_get_contents($oldGameFile), true);
                if (isset($oldData['letter'])) {
                    $usedLetters[] = $oldData['letter'];
                }
            }
        }
        // Türkçede başta kullanılabilen harfler (Ğ, Q, W, X hariç)
        $letters = array('A','B','C','Ç','D','E','F','G','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z');
        $availableLetters = array_values(array_diff($letters, $usedLetters));
        $gameData = file_exists($this->gameFile) ? json_decode(file_get_contents($this->gameFile), true) : [];
        if (!isset($gameData['letter']) || $gameData['letter'] === '' || !in_array($gameData['letter'], $letters)) {
            if (count($availableLetters) > 0) {
                $selectedLetter = $availableLetters[array_rand($availableLetters)];
            } else {
                $selectedLetter = $letters[array_rand($letters)]; // fallback, teorik olarak olmamalı
            }
            $gameData['letter'] = $selectedLetter;
            file_put_contents($this->gameFile, json_encode($gameData));
        } else {
            $selectedLetter = $gameData['letter'];
        }
        include __DIR__ . '/../templates/isimsehir_form.php';
    }
}
