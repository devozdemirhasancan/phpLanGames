<?php
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player = trim($_POST['player']);
    $room = trim($_POST['room']);
    $rounds = isset($_POST['rounds']) ? (int)$_POST['rounds'] : 5;
    $game_type = isset($_POST['game_type']) ? $_POST['game_type'] : 'isim_sehir';
    // Oda var mı kontrol et
    $stmt = $db->prepare('SELECT * FROM rooms WHERE room_name = ?');
    $stmt->execute([$room]);
    $roomRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$roomRow) {
        // Oda yoksa oluştur
        $stmt = $db->prepare('INSERT INTO rooms (room_name, owner, rounds, game_type) VALUES (?, ?, ?, ?)');
        $stmt->execute([$room, $player, $rounds, $game_type]);
    }
    // Oyuncu ekle (varsa tekrar eklemez)
    $stmt = $db->prepare('SELECT * FROM players WHERE room_name = ? AND player_name = ?');
    $stmt->execute([$room, $player]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt = $db->prepare('INSERT INTO players (room_name, player_name) VALUES (?, ?)');
        $stmt->execute([$room, $player]);
    }
    header('Location: game.php?room=' . urlencode($room) . '&player=' . urlencode($player));
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>İsim-Şehir-Hayvan-Bitki Oyunu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h1 class="card-title text-center mb-4">İsim-Şehir-Hayvan-Bitki</h1>
                    <form action="" method="post" id="joinForm">
                        <div class="mb-3">
                            <label for="player" class="form-label">Oyuncu Adı:</label>
                            <input type="text" id="player" name="player" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="room" class="form-label">Oda Adı:</label>
                            <input type="text" id="room" name="room" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="game_type" class="form-label">Oyun Türü:</label>
                            <select id="game_type" name="game_type" class="form-select" required>
                                <option value="isim_sehir">İsim-Şehir-Hayvan-Bitki</option>
                                <option value="kelime_zinciri">Kelime Zinciri</option>
                            </select>
                        </div>
                        <div class="mb-3" id="roundsDiv" style="display:none;">
                            <label for="rounds" class="form-label">Tur Sayısı:</label>
                            <input type="number" id="rounds" name="rounds" class="form-control" min="1" max="20" value="5">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Odaya Katıl</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
window.addEventListener('DOMContentLoaded', function() {
    // Rastgele isim üretici
    function randomName() {
        const names = ["Kartal","Aslan","Yıldız","Bulut","Deniz","Güneş","Kuzey","Mavi","Ada","Baran","Ekin","Lale","Neva","Sarp","Tuna","Yamaç","Zeynep","Efe","Duru","Lina","Mira","Rüzgar","Toprak","Yasmin","Atlas","Arya","Lara","Alp","Ekin","Bora","Sena"];
        return names[Math.floor(Math.random() * names.length)] + Math.floor(Math.random()*1000);
    }
    var playerInput = document.getElementById('player');
    if (playerInput) {
        let storedName = localStorage.getItem('isimsehir_player');
        if (!storedName) {
            storedName = randomName();
            localStorage.setItem('isimsehir_player', storedName);
        }
        playerInput.value = storedName;
        playerInput.addEventListener('input', function() {
            localStorage.setItem('isimsehir_player', playerInput.value);
        });
    }
    const roomInput = document.getElementById('room');
    const roundsDiv = document.getElementById('roundsDiv');
    if (roomInput) {
        roomInput.addEventListener('blur', function() {
            const room = roomInput.value.trim();
            if (!room) return;
            fetch('room_check.php?room=' + encodeURIComponent(room))
                .then(r => r.json())
                .then(data => {
                    if (data.exists) {
                        roundsDiv.style.display = 'none';
                    } else {
                        roundsDiv.style.display = 'block';
                    }
                });
        });
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
