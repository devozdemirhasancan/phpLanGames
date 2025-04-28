<?php $pageTitle = "İsim-Şehir-Hayvan-Bitki"; include __DIR__.'/common_header.php'; ?>
<div>
    <h4 class="mb-3">Harf: <span class="badge bg-primary"><?php echo htmlspecialchars($selectedLetter); ?></span></h4>
    <div class="mb-3">
        <span id="timer" class="badge bg-danger fs-5">60</span> saniye kaldı
    </div>
    <?php if (!isset($gameData['answers'][$this->player])): ?>
    <form method="post" id="answerForm">
        <input type="hidden" name="player" value="<?php echo htmlspecialchars($this->player); ?>">
        <input type="hidden" name="room" value="<?php echo htmlspecialchars($this->room); ?>">
        <div class="mb-2">
            <label class="form-label">İsim</label>
            <input name="isim" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Şehir</label>
            <input name="sehir" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Hayvan</label>
            <input name="hayvan" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Bitki</label>
            <input name="bitki" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Nesne</label>
            <input name="nesne" class="form-control">
        </div>
        <div class="mb-2">
            <label class="form-label">Ünlü İsmi</label>
            <input name="unlu" class="form-control">
        </div>
        <div class="mb-2">
            <label class="form-label">Marka</label>
            <input name="marka" class="form-control">
        </div>
        <div class="mb-2">
            <label class="form-label">Renk</label>
            <input name="renk" class="form-control">
        </div>
        <div class="mb-2">
            <label class="form-label">Yemek</label>
            <input name="yemek" class="form-control">
        </div>
        <button type="submit" class="btn btn-success w-100">Gönder</button>
    </form>
    <script>
    let timeLeft = 60;
    const timerEl = document.getElementById('timer');
    const form = document.getElementById('answerForm');
    const countdown = setInterval(() => {
        timeLeft--;
        timerEl.textContent = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(countdown);
            form.submit();
        }
    }, 1000);
    </script>
    <?php else: ?>
    <div class="alert alert-info mt-3 text-center">
        <div class="mb-2">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Yükleniyor...</span>
            </div>
        </div>
        <strong>Cevabınız kaydedildi.</strong><br>
        Diğer oyuncuların bitirmesi bekleniyor...
        <div class="mt-2" id="waitingStatus">
            <small>Kalan oyuncu sayısı hesaplanıyor...</small>
        </div>
    </div>
    <script>
    // Cevap durumu ve otomatik yönlendirme
    function checkGameStatus() {
        fetch('game_status.php?room=<?php echo urlencode($this->room); ?>&round=<?php echo $this->currentRound; ?>')
            .then(r => r.json())
            .then(data => {
                if (data.finished) {
                    window.location.reload();
                } else {
                    let kalan = data.total - data.answered;
                    document.getElementById('waitingStatus').innerHTML =
                        '<small>Kalan oyuncu: <strong>' + kalan + '</strong> / ' + data.total + '</small>';
                }
            });
    }
    setInterval(checkGameStatus, 2000);
    checkGameStatus();
    </script>
    <?php endif; ?>
</div>
</body>
</html>
