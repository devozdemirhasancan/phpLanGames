<?php $pageTitle = "Lobi"; include __DIR__.'/common_header.php'; ?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8">
        <div class="card shadow mb-4">
            <div class="card-body">
                <h2 class="card-title text-center mb-3">Lobi: <?php echo htmlspecialchars($room); ?></h2>
                <div class="mb-3">
                    <label class="form-label">Lobi Linki:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($lobiLink); ?>" readonly onclick="this.select();">
                    <small class="form-text text-muted">Lobiye katılacaklar için: Linkin sonuna kullanıcı adını ekleyin. Örn: <?php echo htmlspecialchars($lobiLink); ?>Ali</small>
                </div>
                <h5>Oyuncular:</h5>
                <ul class="list-group mb-3" id="playerList">
                    <?php foreach ($players as $p): ?>
                        <li class="list-group-item<?php if ($p === $owner) echo ' list-group-item-primary'; ?><?php if ($p === $player) echo ' list-group-item-success'; ?>">
                            <?php echo htmlspecialchars($p); ?><?php if ($p === $owner) echo ' <span class=\'badge bg-primary\'>Oda Sahibi</span>'; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mb-3">Tur Sayısı: <strong><?php echo $rounds; ?></strong></div>
                <?php if ($player === $owner): ?>
                    <form id="startGameForm">
                        <input type="hidden" name="room" value="<?php echo htmlspecialchars($room); ?>">
                        <input type="hidden" name="player" value="<?php echo htmlspecialchars($player); ?>">
                        <button type="submit" class="btn btn-success w-100">Oyunu Başlat</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">Oda sahibi oyunu başlatana kadar bekleyiniz...</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
// Oyuncu listesi otomatik güncellensin
setInterval(function() {
    fetch('lobi_players.php?room=<?php echo urlencode($room); ?>')
        .then(r => r.json())
        .then(data => {
            let html = '';
            data.players.forEach(function(p) {
                let classes = 'list-group-item';
                if (p === '<?php echo $owner; ?>') classes += ' list-group-item-primary';
                if (p === '<?php echo $player; ?>') classes += ' list-group-item-success';
                html += '<li class="' + classes + '">' + p + (p === '<?php echo $owner; ?>' ? ' <span class="badge bg-primary">Oda Sahibi</span>' : '') + '</li>';
            });
            document.getElementById('playerList').innerHTML = html;
        });
}, 2000);
// Oyun başlatıldı mı kontrolü ve yönlendirme
setInterval(function() {
    fetch('room_check.php?room=<?php echo urlencode($room); ?>')
        .then(r => r.json())
        .then(data => {
            if (data.started) {
                window.location.href = 'game.php?room=<?php echo urlencode($room); ?>&player=<?php echo urlencode($player); ?>&start=1';
            }
        });
}, 2000);
// Oda sahibi için: Oyunu başlatınca start_game.php'ye istek at
const startGameForm = document.getElementById('startGameForm');
if (startGameForm) {
    startGameForm.addEventListener('submit', function(e) {
        e.preventDefault();
        fetch('start_game.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'room=' + encodeURIComponent('<?php echo $room; ?>')
        }).then(r => r.json()).then(data => {
            if (data.success) {
                window.location.href = 'game.php?room=<?php echo urlencode($room); ?>&player=<?php echo urlencode($player); ?>&start=1';
            }
        });
    });
}
</script>
</body>
</html>
