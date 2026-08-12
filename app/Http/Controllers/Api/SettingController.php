<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function getLatestVersion(Request $request)
    {

        $deviceType = $request->input('device_type') ?? "ios";

        if($deviceType === 'android') {
            $latestVersion = Setting::where('attribute', 'android_latest_version')->value('value');
        }else{
            $latestVersion = Setting::where('attribute', 'ios_latest_version')->value('value');
        }

        if (!$latestVersion) {
            return sendResponse(null, 404, 'Latest version not found');
        }

        return sendResponse([
            'latest_version' => $latestVersion,
        ], 200);
    }
}
