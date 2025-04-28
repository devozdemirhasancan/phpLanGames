<?php
require_once 'db.php';
$room = isset($_GET['room']) ? trim($_GET['room']) : (isset($_POST['room']) ? trim($_POST['room']) : '');
$player = isset($_GET['player']) ? trim($_GET['player']) : (isset($_POST['player']) ? trim($_POST['player']) : '');
if (!$player) {
    // Eğer isim yoksa, otomatik isim atama ve yönlendirme için JS modalı gösterilecek
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>İsim Girişi - İsim-Şehir-Hayvan-Bitki</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">İsim Giriniz</h2>
                        <form id="nameForm">
                            <div class="mb-3">
                                <label for="playerName" class="form-label">Oyuncu Adı:</label>
                                <input type="text" id="playerName" name="playerName" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Devam Et</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    function randomName() {
        const names = ["Kartal","Aslan","Yıldız","Bulut","Deniz","Güneş","Kuzey","Mavi","Ada","Baran","Ekin","Lale","Neva","Sarp","Tuna","Yamaç","Zeynep","Efe","Duru","Lina","Mira","Rüzgar","Toprak","Yasmin","Atlas","Arya","Lara","Alp","Ekin","Bora","Sena"];
        return names[Math.floor(Math.random() * names.length)] + Math.floor(Math.random()*1000);
    }
    window.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('playerName');
        let storedName = localStorage.getItem('isimsehir_player');
        if (!storedName) {
            storedName = randomName();
            localStorage.setItem('isimsehir_player', storedName);
        }
        input.value = storedName;
        input.addEventListener('input', function() {
            localStorage.setItem('isimsehir_player', input.value);
        });
        document.getElementById('nameForm').addEventListener('submit', function(e) {
            e.preventDefault();
            let name = input.value.trim() || randomName();
            localStorage.setItem('isimsehir_player', name);
            // URL'de player parametresiyle tekrar yönlendir
            const params = new URLSearchParams(window.location.search);
            params.set('player', name);
            window.location.search = params.toString();
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}
// Ortak fonksiyonlar için helper dosyası
require_once __DIR__ . '/includes/game_helpers.php';

// Oda ve oyuncu kontrolü
$roomRow = getRoom($db, $room);
if (!$roomRow) {
    echo '<div class="alert alert-danger">Oda bulunamadı.</div>';
    exit;
}
$owner = $roomRow['owner'];
$rounds = $roomRow['rounds'];
$gameType = isset($roomRow['game_type']) ? $roomRow['game_type'] : 'isim_sehir';

$players = getPlayers($db, $room);
if (!in_array($player, $players)) {
    addPlayer($db, $room, $player);
    $players = getPlayers($db, $room);
}

updatePlayerActive($db, $room, $player);
removeInactivePlayers($db, $room);

// Lobi kontrolü: Oyun başlamadıysa lobi ekranı göster
if (empty($_GET['start']) && !$roomRow['started']) {
    $lobiLink = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/game.php?room=' . urlencode($room) . '&player=';
    include __DIR__ . '/templates/lobi.php';
    exit;
}

// Tur numarasını session ile takip et
session_start();
if (!isset($_SESSION['current_round'])) {
    $_SESSION['current_round'] = [];
}
if (!isset($_SESSION['current_round'][$room])) {
    $_SESSION['current_round'][$room] = [];
}
if (!isset($_SESSION['current_round'][$room][$player]) || !is_numeric($_SESSION['current_round'][$room][$player])) {
    $_SESSION['current_round'][$room][$player] = 1;
}
$currentRound = $_SESSION['current_round'][$room][$player];

// Oyun class adını game_type'a göre belirle
$gameClassMap = [
    'isim_sehir' => 'IsimSehirGame',
    'kelime_zinciri' => 'KelimeZinciriGame',
    'gizli_kelime' => 'GizliKelimeGame',
    'cizim_tahmin' => 'CizimTahminGame',
    'bilgi_yarisi' => 'BilgiYarisiGame',
    'trivia_race' => 'TriviaRaceGame',
    'cumle_kurma' => 'CumleKurmacaGame',
    'kimdir_o' => 'KimdirOGame',
];
$gameClass = isset($gameClassMap[$gameType]) ? $gameClassMap[$gameType] : 'IsimSehirGame';
$gameClassFile = __DIR__ . '/games/' . $gameClass . '.php';
if (!file_exists($gameClassFile)) {
    die('<div class="alert alert-danger">Oyun dosyası bulunamadı.</div>');
}
require_once $gameClassFile;

// Her tur için gameFile tanımı burada ve sadece burada olmalı
$gameFile = __DIR__ . '/data/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $room) . '_game_' . $currentRound . '.json';

// Oyun class'ını başlat
$game = new $gameClass($db, $room, $player, $players, $rounds, $currentRound, $gameFile);
$game->handlePost();
if ($game->isFinished()) {
    if ($currentRound < $rounds) {
        $_SESSION['current_round'][$room][$player] = $currentRound + 1;
        header('Location: game.php?room=' . urlencode($room) . '&player=' . urlencode($player) . '&start=1');
        exit;
    } else {
        header('Location: results.php?room=' . urlencode($room) . '&player=' . urlencode($player));
        exit;
    }
}
$game->render();
?>