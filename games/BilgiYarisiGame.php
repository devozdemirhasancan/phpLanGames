<?php
require_once __DIR__ . '/GameBase.php';
class BilgiYarisiGame extends GameBase {
    public function handlePost() {
        // ...implement logic for quiz answering...
    }
    public function isFinished() {
        // ...implement finish logic...
        return false;
    }
    public function render() {
        include __DIR__ . '/../templates/bilgiyarisi_form.php';
    }
}
