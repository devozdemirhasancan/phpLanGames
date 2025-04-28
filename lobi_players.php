<?php
require_once 'db.php';
require_once __DIR__ . '/includes/game_helpers.php';
$room = isset($_GET['room']) ? trim($_GET['room']) : '';
header('Content-Type: application/json');
echo json_encode([
    'players' => getPlayers($db, $room)
]);
