<?php
require_once 'db.php';
require_once __DIR__ . '/includes/game_helpers.php';
$room = isset($_GET['room']) ? trim($_GET['room']) : '';
$stmt = $db->prepare('SELECT COUNT(*) FROM rooms WHERE room_name = ?');
$stmt->execute([$room]);
$exists = $stmt->fetchColumn() > 0;
$started = false;
if ($exists) {
    $stmt2 = $db->prepare('SELECT started FROM rooms WHERE room_name = ?');
    $stmt2->execute([$room]);
    $started = (bool)$stmt2->fetchColumn();
}
header('Content-Type: application/json');
echo json_encode(['exists' => $exists, 'started' => $started]);
