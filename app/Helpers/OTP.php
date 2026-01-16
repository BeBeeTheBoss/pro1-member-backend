<?php

use Carbon\Carbon;
use App\Models\RequestedOtp;
use Illuminate\Support\Facades\Http;

function generateOtp($phone)
{
    $token = env('SMSPoh_TOKEN');
    $end_point = env('SMSPoh_ENDPOINT');
    $formatted_phone_number = formatPhoneNumber($phone);
    $otp = rand(100000, 999999);

    RequestedOtp::where('phone', $phone)->delete();

    RequestedOtp::create([
        'phone' => $phone,
        'otp' => $otp,
        'expire_at' => Carbon::now()->addMinutes(5)
    ]);

    sendOtp($token, $end_point, $formatted_phone_number, $otp);
}

function sendOtp($token, $end_point, $formatted_phone_number, $otp)
{

    return Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->post($end_point, [
        'to' => $formatted_phone_number,
        'message' => "Your register OTP code is " . $otp . " for PRO 1 MM Member.",
        'from' => 'PRO1 MM'
    ]);
}

function formatPhoneNumber($phone)
{
    $start_number = (substr($phone, 0, 1));

    if ($start_number == 0) {
        $formatted_phone_number = "95" . ltrim($phone, "0");
    } else {
        $formatted_phone_number = $phone;
    }

    $length = strlen($formatted_phone_number);

    // if ($length == 10 || $length == 12) {
    //     return $formatted_phone_number;
    // }

    return $formatted_phone_number;
}
