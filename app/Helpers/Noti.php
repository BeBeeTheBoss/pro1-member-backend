<?php

use Illuminate\Support\Facades\Http;

function sendPushNotification($token,$title,$message)
{
    Http::withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->post('https://exp.host/--/api/v2/push/send', [
        'to' => $token,
        'sound' => 'default',
        'title' => $title,
        'body' => $message,
    ]);
}
