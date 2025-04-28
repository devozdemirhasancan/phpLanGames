<?php
require_once __DIR__ . '/GameBase.php';
class GizliKelimeGame extends GameBase {
    public function handlePost() {
        // ...implement logic for clue/guessing...
    }
    public function isFinished() {
        // ...implement finish logic...
        return false;
    }
    public function render() {
        include __DIR__ . '/../templates/gizlikelime_form.php';
    }
}
