<?php


use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;

class SetSpeedConversation extends Conversation
{
    protected $speed;

    public function askSpeed()
    {
        $this->ask('Введите желаемую скорость печати текста. От 1 до 10', function(Answer $answer) {
            $speed = $answer->getText();
            if (preg_match('/\d/', $speed) === 1 AND $speed >= 0 AND $speed <= 10) {
                $this->speed = $answer->getText();
                $this->say('Число должно быть от 0 до 10');
            } else {
                $this->say('Записал');
            }

        });
    }
    public function run()
    {
        $this->askSpeed();
    }
}