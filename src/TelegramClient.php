<?php


namespace Bot;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class TelegramClient
{
    public static function sendGif($chatId, $gifFile, $telegramToken, $telegramProxy = null): bool
    {
        $client = new Client([
            'base_uri' => "https://api.telegram.org/bot{$telegramToken}/"
        ]);
        $requestOptions = [
            'multipart' => self::toMultiPart([
                'chat_id' => $chatId,
                'animation' => $gifFile
            ])
        ];
        if (!is_null($telegramProxy)) {
            $requestOptions['proxy'] = $telegramProxy;
        }
        try {
            $result = $client->request(
                'POST',
                'sendAnimation',
                $requestOptions
            );
        } catch (GuzzleException $e) {
            return false;
        }
        return $result->getStatusCode() === 200;
    }
    private static function toMultiPart(array $arr): array
    {
        $result = [];
        array_walk($arr, function($value, $key) use(&$result) {
            $result[] = ['name' => $key, 'contents' => $value];
        });
        return $result;
    }
}