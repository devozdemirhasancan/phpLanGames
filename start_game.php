<?php
require_once 'db.php';
require_once __DIR__ . '/includes/game_helpers.php';
$room = isset($_POST['room']) ? trim($_POST['room']) : '';
header('Content-Type: application/json');
if ($room) {
    $stmt = $db->prepare('UPDATE rooms SET started = 1 WHERE room_name = ?');
    $stmt->execute([$room]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'room parametresi gerekli']);
}
