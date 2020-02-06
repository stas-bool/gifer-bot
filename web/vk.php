<?php

$message = json_decode(file_get_contents('php://input'), true);
if ($message['type'] == 'confirmation' && $message['group_id'] == 191703401) {
    return '7a619544';
}
