<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingController extends Controller
{
    public function getLatestVersion()
    {
        $latestVersion = Setting::where('attribute', 'latest_version')->value('value');

        if (!$latestVersion) {
            return sendResponse(null, 404, 'Latest version not found');
        }

        return sendResponse([
            'latest_version' => $latestVersion,
        ], 200);
    }
}
