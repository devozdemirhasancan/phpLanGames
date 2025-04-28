<?php $pageTitle = "Kelime Zinciri"; include __DIR__.'/common_header.php'; ?>
<div>
    <h5 class="mb-2">
        <?php if ($ilkKelimeMi && $isMyTurn): ?>
            Başlangıç Harfi: <span class="badge bg-primary"><?php echo htmlspecialchars($sonKelime); ?></span>
        <?php elseif (!$ilkKelimeMi): ?>
            Son Kelime: <span class="badge bg-primary"><?php echo htmlspecialchars($sonKelime); ?></span>
        <?php endif; ?>
    </h5>
    <div class="mb-3">
        <strong>Sıra: </strong> <span class="badge bg-info"><?php echo htmlspecialchars($currentPlayer); ?></span>
    </div>
    <?php if ($isMyTurn): ?>
        <div class="mb-3">
            <span id="timer" class="badge bg-danger fs-5"><?php echo $sure; ?></span> saniye kaldı
        </div>
        <?php if (isset($_SESSION['kelime_hata'])): ?>
            <div class="alert alert-danger text-center"><?php echo $_SESSION['kelime_hata']; ?></div>
        <?php endif; ?>
        <form method="post" id="kelimeForm">
            <input type="hidden" name="player" value="<?php echo htmlspecialchars($this->player); ?>">
            <input type="hidden" name="room" value="<?php echo htmlspecialchars($this->room); ?>">
            <div class="mb-2">
                <label class="form-label">
                    <?php if ($ilkKelimeMi): ?>
                        "<?php echo htmlspecialchars($sonKelime); ?>" harfiyle başlayan kelime:
                    <?php else: ?>
                        Son harfi <b><?php echo mb_substr($sonKelime, -1); ?></b> ile başlayan kelime:
                    <?php endif; ?>
                </label>
                <input name="kelime" class="form-control" required autocomplete="off">
            </div>
            <button type="submit" class="btn btn-success w-100">Gönder</button>
        </form>
        <script>
        let timeLeft = <?php echo $sure; ?>;
        const timerEl = document.getElementById('timer');
        const form = document.getElementById('kelimeForm');
        const firstLetter = '<?php echo mb_strtolower($ilkKelimeMi ? $sonKelime : mb_substr($sonKelime, -1)); ?>';
        form.kelime.addEventListener('input', function() {
            if (!form.kelime.value.toLowerCase().startsWith(firstLetter)) {
                form.kelime.setCustomValidity('Kelime "' + firstLetter + '" harfiyle başlamalı!');
            } else {
                form.kelime.setCustomValidity('');
            }
        });
        const countdown = setInterval(() => {
            timeLeft--;
            timerEl.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(countdown);
                // Süre dolduysa otomatik pas için form gönder
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'auto_pass';
                input.value = '1';
                form.appendChild(input);
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
            <strong>Sıra: <?php echo htmlspecialchars($currentPlayer); ?></strong><br>
            <?php if ($ilkKelimeMi): ?>
                Başlangıç harfiyle kelime yazılması bekleniyor...
            <?php else: ?>
                Zincirin devamı bekleniyor...
            <?php endif; ?>
        </div>
        <script>
        setInterval(function() { window.location.reload(); }, 2000);
        </script>
    <?php endif; ?>
    <div class="mt-4">
        <h6>Zincir:</h6>
        <ul class="list-group">
            <?php foreach ($zincir as $i => $item): ?>
                <li class="list-group-item">
                    #<?php echo $i+1; ?>:
                    <strong><?php echo htmlspecialchars($item['kelime']); ?></strong>
                    <span class="text-muted">(<?php echo htmlspecialchars($item['player']); ?>)</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
</body>
</html>
<?php unset($_SESSION['kelime_hata']); ?>
