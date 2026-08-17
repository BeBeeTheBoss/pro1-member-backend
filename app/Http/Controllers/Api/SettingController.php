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

    public function getIsForceUpdate(Request $request){
        $deviceType = $request->input('device_type') ?? "ios";

        if($deviceType === 'android') {
            $isForceUpdate = Setting::where('attribute', 'is_android_force_update')->value('value');
        }else{
            $isForceUpdate = Setting::where('attribute', 'is_ios_force_update')->value('value');
        }

        if (!$isForceUpdate) {
            return sendResponse(null, 404, 'Force update information not found');
        }

        return sendResponse([
            'is_force_update' => $isForceUpdate,
        ], 200);
    }

    public function getIsUnderMaintenance(Request $request){
        $isUnderMaintenance = Setting::where('attribute', 'is_under_maintenance')->value('value');

        if (!$isUnderMaintenance) {
            return sendResponse(null, 404, 'Under maintenance information not found');
        }

        return sendResponse([
            'is_under_maintenance' => $isUnderMaintenance,
        ], 200);
    }

}
