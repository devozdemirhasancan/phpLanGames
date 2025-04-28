<?php
// Ortak oyun yardımcı fonksiyonları
function getRoom($db, $room) {
    $stmt = $db->prepare('SELECT * FROM rooms WHERE room_name = ?');
    $stmt->execute([$room]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getPlayers($db, $room) {
    $stmt = $db->prepare('SELECT player_name FROM players WHERE room_name = ? ORDER BY joined_at ASC');
    $stmt->execute([$room]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
function hasLastActiveColumn($db) {
    $result = $db->query("PRAGMA table_info(players)");
    foreach ($result as $row) {
        if ($row['name'] === 'last_active') return true;
    }
    return false;
}
function addLastActiveColumnIfNeeded($db) {
    if (!hasLastActiveColumn($db)) {
        $db->exec('ALTER TABLE players ADD COLUMN last_active DATETIME');
    }
}
addLastActiveColumnIfNeeded($db);
function addPlayer($db, $room, $player) {
    if (hasLastActiveColumn($db)) {
        $stmt = $db->prepare('INSERT OR IGNORE INTO players (room_name, player_name, last_active) VALUES (?, ?, CURRENT_TIMESTAMP)');
        $stmt->execute([$room, $player]);
    } else {
        $stmt = $db->prepare('INSERT OR IGNORE INTO players (room_name, player_name) VALUES (?, ?)');
        $stmt->execute([$room, $player]);
    }
}
function updatePlayerActive($db, $room, $player) {
    if (hasLastActiveColumn($db)) {
        $stmt = $db->prepare('UPDATE players SET last_active = CURRENT_TIMESTAMP WHERE room_name = ? AND player_name = ?');
        $stmt->execute([$room, $player]);
    }
}
function removeInactivePlayers($db, $room) {
    if (hasLastActiveColumn($db)) {
        $stmt = $db->prepare('DELETE FROM players WHERE room_name = ? AND (last_active IS NULL OR last_active < datetime("now", "-3 minutes"))');
        $stmt->execute([$room]);
    }
}
