<?php


namespace Bot;


use GuzzleHttp\Client;

trait GifSenderTrait
{
    public static function sendGif($chatId, $gifFile, $telegramConfig)
    {
        $telegramToken = $telegramConfig['token'];
        $client = new Client([
            'base_uri' => "https://api.telegram.org/bot{$telegramToken}/"
        ]);
        $requestOptions = [
            'multipart' => self::toMultiPart([
                'chat_id' => $chatId,
                'animation' => fopen($gifFile, 'r')
            ])
        ];
        if (isset($telegramConfig['proxy'])) {
            $requestOptions['proxy'] = $telegramConfig['proxy'];
        }
        $client->request(
            'POST',
            'sendAnimation',
            $requestOptions
        );
    }
    private static function toMultiPart(array $arr) {
        $result = [];
        array_walk($arr, function($value, $key) use(&$result) {
            $result[] = ['name' => $key, 'contents' => $value];
        });
        return $result;
    }
}