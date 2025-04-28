<?php
// db.php - SQLite bağlantısı ve tablo oluşturma
$dbFile = __DIR__ . '/data/isimsehir.sqlite';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Oda tablosu
$db->exec('CREATE TABLE IF NOT EXISTS rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name TEXT UNIQUE,
    owner TEXT,
    rounds INTEGER,
    started INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');
// Oyuncu tablosu
$db->exec('CREATE TABLE IF NOT EXISTS players (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name TEXT,
    player_name TEXT,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');
// Oyun cevapları tablosu
$db->exec('CREATE TABLE IF NOT EXISTS answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_name TEXT,
    round INTEGER,
    player_name TEXT,
    letter TEXT,
    isim TEXT,
    sehir TEXT,
    hayvan TEXT,
    bitki TEXT,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');
// Oyun türleri tablosu
$db->exec('CREATE TABLE IF NOT EXISTS games (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    game_type TEXT UNIQUE,
    display_name TEXT
)');
// rooms tablosuna game_type alanı ekle (varsa hata vermez)
try {
    $db->exec("ALTER TABLE rooms ADD COLUMN game_type TEXT DEFAULT 'isim_sehir'");
} catch (Exception $e) {}

// Oyun türlerini ekle (varsa tekrar eklemez)
$gameTypes = [
    ['kelime_zinciri', 'Kelime Zinciri'],
    ['gizli_kelime', 'Gizli Kelime / İpucu'],
    ['cizim_tahmin', 'Çizim Tahmin'],
    ['bilgi_yarisi', 'Hızlı Bilgi Yarışı'],
    ['trivia_race', 'Yarışmacı Yarışı'],
    ['cumle_kurma', 'Anlamlı Cümle Kurmaca'],
    ['kimdir_o', 'Kimdir O?']
];
$stmt = $db->prepare('INSERT OR IGNORE INTO games (game_type, display_name) VALUES (?, ?)');
foreach ($gameTypes as $g) {
    $stmt->execute([$g[0], $g[1]]);
}
