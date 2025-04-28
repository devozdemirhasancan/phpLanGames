<?php
require_once __DIR__ . '/GameBase.php';
class TriviaRaceGame extends GameBase {
    public function handlePost() {
        // ...implement logic for trivia race answering...
    }
    public function isFinished() {
        // ...implement finish logic...
        return false;
    }
    public function render() {
        include __DIR__ . '/../templates/triviarace_form.php';
    }
}
