<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\SelectedCoupon;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function __construct(protected User $model) {}

    public function register(Request $request)
    {

        DB::beginTransaction();
        try {
            $cloud_db = DB::connection('Cloud');
            $cloud_db->table(table: 'public.gbh_customer')
                ->where('identification_card', $request->idcard)
                ->update([
                    'mobile' => $request->mobile,
                    'full_address' => $request->address,
                    'address_slave' => $request->address,
                    'fullname' => $request->fullname,
                    'tax_code' => $request->tax_code,
                    'date_birthday' => Carbon::parse($request->birthdate)->format('Y-m-d')
                ]);

            $user = $this->model->create([
                'name' => $request->fullname,
                'idcard' => $request->idcard,
                'phone' => $request->mobile,
                'password' => Hash::make($request->password),
                'birth_date' => Carbon::parse($request->birthdate)->format('Y-m-d'),
                'gender' => $request->gender,
                'device_id' => $request->device_id,
                'device_name' => $request->device_name,
                'expo_push_token' => $request->expo_push_token
            ]);

            if ($request->hasFile('image')) {

                $image = $request->file('image');
                $filename = time() . '.' . $request->image->extension();
                Storage::disk('public')->putFileAs('profile_images', $image, $filename);
                $user->image = $filename;
                $user->save();
            }

            Auth::loginUsingId($user->id);
            $token = $user->createToken('auth_token')->plainTextToken;

            $member_info = $cloud_db->table(table: 'public.gbh_customer')
                ->where('identification_card', $request->idcard)
                ->first();

            $user->branch_code = $member_info->branch_code;
            $user->save();

            $branch_name = $cloud_db->table(table: 'public.master_branch')
                ->where('branch_code', $member_info->branch_code)
                ->first()->branch_name;


            $user->image = $user->image != null || $user->image != '' ? url("storage/profile_images/" . $user->image) : null;


            $coupon = $cloud_db->table('coupon_online.ecoupon_hd')
                ->where('approve_active', true)
                ->where('date_expire', '>=', now())
                ->where('coup_type_id', 6)
                ->get();

            // SelectedCoupon::create([
            //     'user_id' => $user->id,
            //     'coupon_id' => $coupon[0]->coup_id,
            //     'expiry_date' => $coupon[0]->date_expire,
            //     'coupon_type' => $coupon[0]->coup_type_id,
            //     'coupon_name' => $coupon[0]->coup_name,
            // ]);

            $member_info = (array) $member_info;
            $member_info['user_profile'] = $user;
            $member_info['branch_name'] = $branch_name;

            $noti_title = "System";
            $noti_message = "Congratulations! You have earned 20 points for your first login.";

            $timestamp = Carbon::now()->getTimestamp() * 1000;

            $json = [
                'memberCardNo' => $request->idcard,
                'timestamp' => (string) $timestamp,
            ];

            $secret_key = env('SECRET_KEY');

            $jsonString = json_encode($json);

            $payload = $jsonString . $secret_key;

            $calculatedHash = hash('sha256', $payload);

            $json['hashValue'] = $calculatedHash;

            info($json);

            $response = Http::post("https://memberuat.sdpghc.net:2004/api/coupon/getFirstCoupon", $json);

            info($response);

            if ($response['success'] === true) {
                sendPushNotification($request->expo_push_token, $noti_title, $noti_message);
            }

            $notification = Notification::create([
                'title' => $noti_title,
                'message' => $noti_message,
                'recipient' => 'specific'
            ]);

            UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id
            ]);

            DB::commit();
            $cloud_db->commit();

        } catch (Exception $e) {
            DB::rollBack();
            $cloud_db->rollBack();
            info($e);
            return sendResponse(null, 500, "Something went wrong");
        }

        return sendResponse([
            'user' => $member_info,
            'token' => $token
        ], 200);
    }

    public function login(Request $request)
    {

        $cloud_db = DB::connection('Cloud');
        $member_info = $cloud_db->table(table: 'public.gbh_customer')
            ->where('mobile', $request->phone)
            ->first();

        if (!$member_info) {
            return sendResponse(null, 404, "User not found");
        }

        $branch_name = $cloud_db->table(table: 'public.master_branch')
            ->where('branch_code', $member_info->branch_code)
            ->first()->branch_name;

        if (!$member_info) {
            return sendResponse(null, 404, "User not found");
        }

        $user = $this->model->where('phone', $member_info->mobile)->first();

        if (!$user) {
            return sendResponse(null, 404, "User not found");
        }

        // if (!$user) {
        //     $user = $this->model->create([
        //         'name' => $member_info->fullname,
        //         'idcard' => $member_info->identification_card,
        //         'phone' => $member_info->mobile,
        //         'password' => $request->password,
        //         'birth_date' => Carbon::parse('2002-11-23')->format('Y-m-d')
        //     ]);

        //     $coupon = $cloud_db->table('coupon_online.ecoupon_hd')
        //         ->where('approve_active', true)
        //         ->where('date_expire', '>=', now())
        //         ->where('coup_type_id', 6)
        //         ->get();

        //     SelectedCoupon::create([
        //         'user_id' => $user->id,
        //         'coupon_id' => $coupon[0]->coup_id,
        //         'expiry_date' => $coupon[0]->date_expire,
        //         'coupon_type' => $coupon[0]->coup_type_id,
        //         'coupon_name' => $coupon[0]->coup_name,
        //     ]);
        // } else {
        if (!Hash::check($request->password, $user->password)) {
            return sendResponse(null, 401, "Wrong password");
        }
        // }

        // $user->device_id = $request->deviceId;
        // $user->device_name = $request->deviceName;
        // $user->save();

        generateOtp($user->phone);

        Auth::loginUsingId($user->id);
        $token = $user->createToken('auth_token')->plainTextToken;


        $user->image = $user->image != null || $user->image != '' ? url("storage/profile_images/" . $user->image) : null;


        $member_info = (array) $member_info;
        $member_info['user_profile'] = $user;
        $member_info['branch_name'] = $branch_name;

        return sendResponse([
            'user' => $member_info,
            'token' => $token
        ], 200);
    }
}
