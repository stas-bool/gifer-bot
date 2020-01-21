<?php


require_once __DIR__ . '/vendor/autoload.php';

function toMultiPart(array $arr) {
    $result = [];
    array_walk($arr, function($value, $key) use(&$result) {
        $result[] = ['name' => $key, 'contents' => $value];
    });
    return $result;
}

$client = new \GuzzleHttp\Client([
    'proxy' => 'socks5://localhost:8888',
    'base_uri' => 'https://api.telegram.org/bot887931185:AAEu_F46a_nR87kKeBRN_tUIvRohO4XklSw/'
]);
$result = $client->request(
    'POST',
    'sendAnimation',
    [
        'multipart' => toMultiPart([
            'chat_id' => 132763295,
            'animation' => fopen('/tmp/text.gif', 'r')
        ])
    ]
);
var_dump($result);
