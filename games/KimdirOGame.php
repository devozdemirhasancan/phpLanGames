<?php
require_once __DIR__ . '/GameBase.php';
class KimdirOGame extends GameBase {
    public function handlePost() {
        // ...implement logic for character guessing...
    }
    public function isFinished() {
        // ...implement finish logic...
        return false;
    }
    public function render() {
        include __DIR__ . '/../templates/kimdiro_form.php';
    }
}
