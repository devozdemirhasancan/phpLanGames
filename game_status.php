<?php
require_once 'db.php';
require_once __DIR__ . '/includes/game_helpers.php';
$room = isset($_GET['room']) ? trim($_GET['room']) : '';
$round = isset($_GET['round']) ? intval($_GET['round']) : 1;
header('Content-Type: application/json');
$total = count(getPlayers($db, $room));
$gameFile = __DIR__ . '/data/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $room) . '_game_' . $round . '.json';
$answered = 0;
if (file_exists($gameFile)) {
    $gameData = json_decode(file_get_contents($gameFile), true);
    if (isset($gameData['answers'])) {
        $answered = count($gameData['answers']);
    }
}
$finished = ($answered >= $total && $total > 0);
echo json_encode([
    'total' => $total,
    'answered' => $answered,
    'finished' => $finished
]);
