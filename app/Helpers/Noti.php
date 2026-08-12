<?php

use Illuminate\Support\Facades\Http;

function sendPushNotification($token,$title,$message)
{
    $tokens = is_array($token) ? $token : [$token];
    $tokens = array_values(array_filter($tokens));

    foreach (array_chunk($tokens, 100) as $chunk) {
        $messages = array_map(fn($pushToken) => [
            'to' => $pushToken,
            'sound' => 'default',
            'title' => $title,
            'body' => $message,
        ], $chunk);

        Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->timeout(15)
            ->retry(2, 500)
            ->post('https://exp.host/--/api/v2/push/send', $messages);
    }
}
