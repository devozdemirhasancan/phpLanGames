<?php
require_once __DIR__ . '/GameBase.php';
class CizimTahminGame extends GameBase {
    public function handlePost() {
        // ...implement logic for drawing/guessing...
    }
    public function isFinished() {
        // ...implement finish logic...
        return false;
    }
    public function render() {
        include __DIR__ . '/../templates/cizimtahmin_form.php';
    }
}
