<?php
require_once 'db.php';
$room = isset($_GET['room']) ? trim($_GET['room']) : '';
$player = isset($_GET['player']) ? trim($_GET['player']) : '';

// Oda ve oyun türü
$stmt = $db->prepare('SELECT rounds, game_type FROM rooms WHERE room_name = ?');
$stmt->execute([$room]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$rounds = (int)($row['rounds'] ?? 1);
$gameType = $row['game_type'] ?? 'isim_sehir';

$stmt = $db->prepare('SELECT player_name FROM players WHERE room_name = ? ORDER BY joined_at ASC');
$stmt->execute([$room]);
$players = $stmt->fetchAll(PDO::FETCH_COLUMN);

$toplamPuan = array_fill_keys($players, 0);
$turSonuclari = [];

if ($gameType === 'kelime_zinciri') {
    require_once __DIR__ . '/games/KelimeZinciriGame.php';
    for ($tur = 1; $tur <= $rounds; $tur++) {
        list($puanlar, $kelimeler) = KelimeZinciriGame::puanla($room, $tur, $players);
        foreach ($players as $p) {
            $toplamPuan[$p] += $puanlar[$p];
        }
        $turSonuclari[] = [
            'tur' => $tur,
            'kelimeler' => $kelimeler,
            'puanlar' => $puanlar
        ];
    }
    $pageTitle = 'Sonuçlar - Kelime Zinciri';
} else {
    // İsim-Şehir oyunu için mevcut kod
    $stmt = $db->prepare('SELECT player_name FROM players WHERE room_name = ? ORDER BY joined_at ASC');
    $stmt->execute([$room]);
    $players = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Yeni puanlama fonksiyonu: Aynı cevap 1 kişideyse 15, birden fazla kişideyse 5 puan
    function puanlariHesapla($answers, $players, $letter) {
        $puanlar = array_fill_keys($players, 0);
        $kategoriler = ["isim", "sehir", "hayvan", "bitki", "nesne", "unlu", "marka", "renk", "yemek"];
        foreach ($kategoriler as $kategori) {
            // Tüm oyuncuların bu kategori için verdiği cevapları topla
            $cevaplar = [];
            foreach ($players as $p) {
                $deger = isset($answers[$p][$kategori]) ? mb_strtoupper(trim($answers[$p][$kategori]), 'UTF-8') : '';
                // Harf kontrolü
                if ($deger !== '' && mb_substr($deger, 0, 1, 'UTF-8') === $letter) {
                    $cevaplar[$p] = $deger;
                } else {
                    $cevaplar[$p] = '';
                }
            }
            // Cevapların kaç kez tekrarlandığını bul
            $frekans = array_count_values(array_filter($cevaplar));
            foreach ($players as $p) {
                $cevap = $cevaplar[$p];
                if ($cevap === '') continue;
                if ($frekans[$cevap] === 1) {
                    $puanlar[$p] += 15;
                } else {
                    $puanlar[$p] += 5;
                }
            }
        }
        return $puanlar;
    }

    $toplamPuan = array_fill_keys($players, 0);
    $turSonuclari = [];
    for ($tur = 1; $tur <= $rounds; $tur++) {
        $gameFile = __DIR__ . '/data/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $room) . '_game_' . $tur . '.json';
        $letter = '';
        $answers = [];
        if (file_exists($gameFile)) {
            $gameData = json_decode(file_get_contents($gameFile), true);
            $letter = isset($gameData['letter']) ? $gameData['letter'] : '';
            $answers = isset($gameData['answers']) ? $gameData['answers'] : [];
        }
        $puanlar = puanlariHesapla($answers, $players, $letter);
        foreach ($players as $p) {
            $toplamPuan[$p] += $puanlar[$p];
        }
        $turSonuclari[] = [
            'tur' => $tur,
            'letter' => $letter,
            'answers' => $answers,
            'puanlar' => $puanlar
        ];
    }
    $pageTitle = 'Sonuçlar - İsim-Şehir-Hayvan-Bitki';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Tüm Turların Sonuçları</h2>
                    <?php if ($gameType === 'kelime_zinciri'): ?>
                        <?php foreach ($turSonuclari as $turSonuc): ?>
                            <h5 class="mt-4">Tur <?= $turSonuc['tur'] ?></h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Oyuncu</th>
                                            <th>Yazdığı Kelime</th>
                                            <th>Puan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($players as $p): ?>
                                        <tr<?php if ($p === $player) echo ' class="table-success"'; ?>>
                                            <td><?= htmlspecialchars($p) ?></td>
                                            <td><?= isset($turSonuc['kelimeler'][$p]) ? htmlspecialchars($turSonuc['kelimeler'][$p]) : '-' ?></td>
                                            <td><strong><?= $turSonuc['puanlar'][$p] ?? 0 ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                        <h4 class="mt-5">Toplam Puanlar</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Oyuncu</th>
                                        <th>Toplam Puan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php arsort($toplamPuan); foreach ($toplamPuan as $p => $puan): ?>
                                    <tr<?php if ($p === $player) echo ' class="table-success"'; ?>>
                                        <td><?= htmlspecialchars($p) ?></td>
                                        <td><strong><?= $puan ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <?php foreach ($turSonuclari as $turSonuc): ?>
                            <h5 class="mt-4">Tur <?php echo $turSonuc['tur']; ?> - Harf: <span class="badge bg-primary"><?php echo htmlspecialchars($turSonuc['letter']); ?></span></h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Oyuncu</th>
                                            <th>İsim</th>
                                            <th>Şehir</th>
                                            <th>Hayvan</th>
                                            <th>Bitki</th>
                                            <th>Nesne</th>
                                            <th>Ünlü İsmi</th>
                                            <th>Marka</th>
                                            <th>Renk</th>
                                            <th>Yemek</th>
                                            <th>Puan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($players as $p): ?>
                                        <tr<?php if ($p === $player) echo ' class="table-success"'; ?>>
                                            <td><?php echo htmlspecialchars($p); ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['isim']) ? htmlspecialchars($turSonuc['answers'][$p]['isim']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['sehir']) ? htmlspecialchars($turSonuc['answers'][$p]['sehir']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['hayvan']) ? htmlspecialchars($turSonuc['answers'][$p]['hayvan']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['bitki']) ? htmlspecialchars($turSonuc['answers'][$p]['bitki']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['nesne']) ? htmlspecialchars($turSonuc['answers'][$p]['nesne']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['unlu']) ? htmlspecialchars($turSonuc['answers'][$p]['unlu']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['marka']) ? htmlspecialchars($turSonuc['answers'][$p]['marka']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['renk']) ? htmlspecialchars($turSonuc['answers'][$p]['renk']) : '-'; ?></td>
                                            <td><?php echo isset($turSonuc['answers'][$p]['yemek']) ? htmlspecialchars($turSonuc['answers'][$p]['yemek']) : '-'; ?></td>
                                            <td><strong><?php echo isset($turSonuc['puanlar'][$p]) ? $turSonuc['puanlar'][$p] : 0; ?></strong></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                        <h4 class="mt-5">Toplam Puanlar</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Oyuncu</th>
                                        <th>Toplam Puan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php arsort($toplamPuan); foreach ($toplamPuan as $p => $puan): ?>
                                    <tr<?php if ($p === $player) echo ' class="table-success"'; ?>>
                                        <td><?php echo htmlspecialchars($p); ?></td>
                                        <td><strong><?php echo $puan; ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-secondary">Yeni Oyun</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>