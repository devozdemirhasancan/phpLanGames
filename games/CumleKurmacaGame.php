<?php
require_once __DIR__ . '/GameBase.php';
class CumleKurmacaGame extends GameBase {
    public function handlePost() {
        // ...implement logic for sentence creation...
    }
    public function isFinished() {
        // ...implement finish logic...
        return false;
    }
    public function render() {
        include __DIR__ . '/../templates/cumlekurmaca_form.php';
    }
}
