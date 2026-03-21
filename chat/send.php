<?php
require 'vendor/autoload.php';

$pusher = new Pusher\Pusher(
    'c922bfca140061b3ea91',
    '4f39fcf9d33d7dcb174a',        // ← garde le secret privé
    '2130604',
    ['cluster' => 'eu']
);

$data = json_decode(file_get_contents('php://input'), true);
$pusher->trigger('chat', 'message', ['text' => $data['text']]);

echo json_encode(['status' => 'ok']);
