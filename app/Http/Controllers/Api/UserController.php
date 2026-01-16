<?php

namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\QRCode;
use App\Models\RequestedOtp;
use Illuminate\Http\Request;
use App\Models\UsedCouponLog;
use App\Models\SelectedCoupon;
use App\Models\UserNotification;
use function Laravel\Prompts\info;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\CouponResource;
use App\Models\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Log as FacadesLog;

class UserController extends Controller
{

    public function __construct(protected User $model) {}

    public function getUser(Request $request)
    {

        $cloud_db = DB::connection('Cloud');
        $member_info = $cloud_db->table(table: 'public.gbh_customer')
            ->where('identification_card', $request->idcard)
            ->first();

        $branch_name = $cloud_db->table(table: 'public.master_branch')
            ->where('branch_code', $member_info->branch_code)
            ->first()->branch_name;

        $user = $this->model->where('idcard', $request->idcard)->first();

        if ($request->deviceId && $request->deviceName) {

            if ($user->device_id != $request->deviceId) {
                sendPushNotification($user->expo_push_token, "System", "Your account has been logged in from another device");
            }

            $user->device_id = $request->deviceId;
            $user->device_name = $request->deviceName;
            $user->save();
        }

        if (!$member_info) {
            return sendResponse(null, 404, "User not found");
        }

        $user->image = $user->image != null || $user->image != '' ? url("storage/profile_images/" . $user->image) : null;

        $member_info = (array) $member_info;
        $member_info['user_profile'] = $user;
        $member_info['branch_name'] = $branch_name;

        return sendResponse($member_info, 200);
    }

    public function searchUser(Request $request)
    {
        $searchKey = $request->searchKey;

        $users = $this->model
            ->when($searchKey, function ($query, $searchKey) {
                $query->where(function ($q) use ($searchKey) {
                    $q->whereRaw("LOWER(REPLACE(name, ' ', '')) LIKE ?", ["%{$searchKey}%"])
                        ->orWhereRaw("LOWER(REPLACE(idcard, ' ', '')) LIKE ?", ["%{$searchKey}%"])
                        ->orWhereRaw("LOWER(REPLACE(phone, ' ', '')) LIKE ?", ["%{$searchKey}%"]);
                });
            })
            ->select('id', 'name', 'idcard', 'phone')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

    // public function formatPhoneNumber($phone)
    // {
    //     $start_number = (substr($phone, 0, 1));

    //     if ($start_number == 0) {
    //         $formatted_phone_number = "95" . ltrim($phone, "0");
    //     } else {
    //         $formatted_phone_number = $phone;
    //     }

    //     $length = strlen($formatted_phone_number);

    //     // if ($length == 10 || $length == 12) {
    //     //     return $formatted_phone_number;
    //     // }

    //     return $formatted_phone_number;
    // }

    public function resendOtp(Request $request)
    {
        generateOtp($request->phone);
    }

    // public function generateOtp($phone)
    // {
    //     $token = env('SMSPoh_TOKEN');
    //     $end_point = env('SMSPoh_ENDPOINT');
    //     $formatted_phone_number = $this->formatPhoneNumber($phone);
    //     $otp = rand(100000, 999999);

    //     FacadesLog::info($formatted_phone_number);

    //     RequestedOtp::where('phone', $phone)->delete();

    //     RequestedOtp::create([
    //         'phone' => $phone,
    //         'otp' => $otp,
    //         'expire_at' => Carbon::now()->addMinutes(5)
    //     ]);

    //     $this->sendOtp($token, $end_point, $formatted_phone_number, $otp);
    // }

    // function sendOtp($token, $end_point, $formatted_phone_number, $otp)
    // {

    //     return Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $token,
    //     ])->post($end_point, [
    //         'to' => $formatted_phone_number,
    //         'message' => "Your register OTP code is " . $otp . " for PRO 1 MM Member.",
    //         'from' => 'PRO1 MM'
    //     ]);
    // }

    public function verifyOtp(Request $request)
    {

        $requested_otp_data = RequestedOtp::where('phone', $request->phone)->latest()->first();

        if ($requested_otp_data->otp != $request->otp) {
            return sendResponse(null, 405, "OTP Code is invalid");
        }

        if ($requested_otp_data->expire_at < Carbon::now()) {
            return sendResponse(null, 405, "OTP Code is expired");
        }

        return sendResponse(null, 200);
    }


    public function findMember(Request $request)
    {
        $cloud_db = DB::connection('Cloud');

        $member_info = $cloud_db->table(table: 'public.gbh_customer')
            ->when($request->mode, function ($query) use ($request) {

                if ($request->mode === 'phone') {
                    $query->where('mobile', $request->phone);
                } else if ($request->mode === 'id') {
                    $query->where('identification_card', $request->idcard);
                }
            })
            ->first();

        if (!$member_info) {
            return sendResponse(null, 404, "Member not found");
        }

        if ($request->to === 'register') {
            if ($this->model->where('idcard', $member_info->identification_card)->first()) {
                return sendResponse(null, 404, "Member account already exists");
            }
        }

        generateOtp($member_info->mobile);

        return sendResponse($member_info, 200);
    }

    public function changePassword(Request $request)
    {

        $user = $this->model->where('idcard', $request->idcard)->first();

        if (!$user) {
            return sendResponse(null, 404, "User not found");
        }

        if (!Hash::check($request->oldPassword, $user->password)) {
            return sendResponse(null, 401, "Wrong password");
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return sendResponse(null, 200, "Password changed successfully");
    }

    public function forgotPassword(Request $request)
    {
        $user = $this->model->where('idcard', $request->idcard)->first();

        if (!$user) {
            return sendResponse(null, 404, "User not found");
        }

        $user->password = Hash::make($request->newPassword);
        $user->save();

        return sendResponse(null, 200, "Password changed successfully");
    }

    public function updateUser(Request $request)
    {

        $cloud_db = DB::connection('Cloud');

        $cloud_db->table(table: 'public.gbh_customer')
            ->where('identification_card', $request->idcard)
            ->update([
                'address_slave' => $request->address,
                'full_address' => $request->address
            ]);

        $user = $this->model->where('idcard', $request->idcard)->first();

        if (!$user) {
            return sendResponse(null, 404, "User not found");
        }

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $filename = time() . '.' . $request->image->extension();
            Storage::disk('public')->putFileAs('profile_images', $image, $filename);
            Storage::disk('public')->delete('profile_images/' . $user->image);
            $user->image = $filename;
        }

        $user->gender = $request->gender;
        $user->save();

        $user->image = url("storage/profile_images/" . $user->image);

        return sendResponse($user, 200, "Profile updated successfully");
    }


    public function getPoints(Request $request)
    {
        $cloud_db = DB::connection('Cloud');

        $scores = $cloud_db->table('imember.imember_score')
            ->orderBy('date_expire', 'asc')
            ->where('idcard', $request->idcard)
            ->where('score_balance', '>', 0);

        $scores = $request->byData ? $scores->get() : $scores->sum('score_balance');

        return sendResponse($scores, 200);
    }

    public function useCoupon(Request $request)
    {
        $cloud_db = DB::connection('Cloud');

        $coupon = $cloud_db->table('coupon_online.ecoupon_hd')
            ->where('coup_id', $request->coupon_id)
            ->first();

        $user = $this->model->where('idcard', $request->idcard)->first();
        $member_info = $cloud_db->table('public.gbh_customer')
            ->where('identification_card', $request->idcard)
            ->first();

        DB::beginTransaction();
        $cloud_db->beginTransaction();
        try {
            UsedCouponLog::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon->coup_id,
                'coupon_type' => $coupon->coup_type_id,
                'points' => $coupon->coup_value
            ]);

            switch ($coupon->coup_type_id) {
                case 1:
                    $prefix = 'HB';
                    SelectedCoupon::where('user_id', $user->id)->where('coupon_id', $coupon->coup_id)->update(['is_used' => true, 'used_at' => Carbon::now()]);
                    break;
                case 6:
                    $prefix = 'WP';
                    SelectedCoupon::where('user_id', $user->id)->where('coupon_id', $coupon->coup_id)->update(['is_used' => true, 'used_at' => Carbon::now()]);
                    $user->isFLPUsed = true;
                    $user->save();
                    break;
            }

            $ref_doc = $prefix . str_replace(['-', ':', '.', ' '], '', Carbon::now()->format('y-m-d h:i:s.u'));

            $cloud_db->table('imember.imember_score')->insert([
                'gbh_customer_id' => $member_info->gbh_customer_id,
                'ref_no' => $ref_doc,
                'ref_id' => $coupon->coup_id,
                'score_flag' => 0,
                'score' => 0,
                'score_net' => $coupon->coup_value,
                'customer_barcode' =>  $member_info->customer_barcode,
                'date_now' => Carbon::now(),
                'branch_code' => $member_info->branch_code,
                'idcard' => $member_info->identification_card,
                'score_balance' => $coupon->coup_value,
                'date_expire' => $coupon->date_expire,
                'customer_rank_id' => $member_info->customer_rank_id
            ]);

            DB::commit();
            $cloud_db->commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $cloud_db->rollBack();
            info($e->getMessage());
            return sendResponse(null, 500, $e->getMessage());
        }
    }


    public function getHistories($idcard, $limit)
    {
        $user = $this->model->where('idcard', $idcard)->first();
        $userId = $user->id;

        $histories = SelectedCoupon::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            // ->where(function ($query) {
            //     $query->where('is_used', true)
            //         ->orWhere(function ($query) {
            //             $query->where('expiry_date', '>=', Carbon::now());
            //         });
            // })
            // ->orderBy('expiry_date', 'desc')
            ->limit($limit)
            ->orderBy('updated_at', 'desc')
            ->get();

        return sendResponse(
            $histories->map(fn($history) => new CouponResource($history, $user)),
            200
        );
    }

    public function getAvailableCupons($idcard)
    {

        $cloud_db = DB::connection('Cloud');

        $member_info = $cloud_db->table('public.gbh_customer')
            ->where('identification_card', $idcard)
            ->first();

        $user = $this->model->where('idcard', $idcard)->first();

        $selectCouponIDs = SelectedCoupon::where('user_id', $user->id)
            // ->where('expiry_date', '>=', now())
            ->pluck('coupon_id');

        $coupons = $cloud_db->table('coupon_online.ecoupon_hd as hd')
            ->join('coupon_online.ecoupon_branch as br', 'br.coup_id', '=', 'hd.coup_id')
            ->where('hd.approve_active', true)
            ->where('hd.date_expire', '>=', now())
            ->whereNotIn('hd.coup_type_id', [1, 6])
            ->whereNotIn('hd.coup_id', $selectCouponIDs)
            ->where('br.coup_branch_code', $member_info->branch_code)
            ->select('hd.*')
            ->distinct()
            ->get();

        return sendResponse($coupons, 200);
    }

    public function selectCoupon(Request $request)
    {

        $user = $this->model->where('idcard', $request->idcard)->first();

        $cloud_db = DB::connection('Cloud');

        $coupon = $cloud_db->table('coupon_online.ecoupon_hd')->where('coup_id', $request->coupon_id)->first();

        SelectedCoupon::create([
            'user_id' => $user->id,
            'coupon_id' => $request->coupon_id,
            'coupon_name' => $coupon->coup_name,
            'coupon_type' => $coupon->coup_type_id,
            'expiry_date' => $coupon->date_expire,
        ]);

        return sendResponse(null, 200);
    }

    public function getCouponDetails($coupon_id)
    {

        $cloud_db = DB::connection('Cloud');

        $coupon = $cloud_db->table('coupon_online.ecoupon_hd as hd')
            ->where('coup_id', $coupon_id)
            ->join('coupon_online.onetime_barcode as bar', 'bar.ecoupon_hd', 'hd.coup_id')
            ->select('bar.*', 'hd.*')
            ->get();

        return sendResponse($coupon, 200);
    }

    public function getMyCoupon($idcard)
    {

        $cloud_db = DB::connection('Cloud');

        $user = $this->model
            ->select(['id', 'idcard', 'birth_date', 'created_at'])
            ->where('idcard', $idcard)
            ->firstOrFail();

        $birthMonth = Carbon::parse($user->birth_date)->month;
        $createdYear = Carbon::parse($user->created_at)->year;

        // info($birthMonth);
        // info($createdYear);

        $birthMonthCoupon = $cloud_db->table('coupon_online.ecoupon_hd')
            ->select('coup_id', 'date_expire', 'coup_type_id', 'coup_name')
            ->where('approve_active', true)
            ->where('coup_type_id', 1)
            ->whereMonth('date_start', $birthMonth)
            ->whereYear('date_start', $createdYear)
            ->first();

        // info($birthMonthCoupon);

        if ($birthMonthCoupon) {
            $exists = SelectedCoupon::where('user_id', $user->id)
                ->where('coupon_id', $birthMonthCoupon->coup_id)
                ->doesntExist();

            $selectedInThisYear = SelectedCoupon::where('user_id', $user->id)
                ->whereYear('created_at', now()->year)
                ->where('coupon_type', 1)
                ->first();

            if ($exists && !$selectedInThisYear) {
                SelectedCoupon::create([
                    'user_id' => $user->id,
                    'coupon_id' => $birthMonthCoupon->coup_id,
                    'expiry_date' => $birthMonthCoupon->date_expire,
                    'coupon_type' => $birthMonthCoupon->coup_type_id,
                    'coupon_name' => $birthMonthCoupon->coup_name
                ]);
            }
        }

        $selectCouponIDs = SelectedCoupon::where('user_id', $user->id)
            // ->where('expiry_date', '>=', now())
            ->pluck('coupon_id');

        $coupons = $cloud_db->table('coupon_online.ecoupon_hd as hd')
            ->leftJoin(
                $cloud_db->raw("
            (
                SELECT DISTINCT ON (ecoupon_hd) *
                FROM coupon_online.onetime_barcode
                ORDER BY ecoupon_hd, one_id DESC
            ) bar
        "),
                'bar.ecoupon_hd',
                '=',
                'hd.coup_id'
            )
            // ->where('hd.approve_active', true)
            // ->where('hd.date_expire', '>=', now())
            ->whereIn('hd.coup_id', $selectCouponIDs)
            ->select('hd.*', 'bar.*')
            ->get()
            ->map(fn($item) => (array) $item);

        return sendResponse(
            $coupons->map(fn($coupon) => new CouponResource($coupon, $user)),
            200
        );
    }

    public function storePointRedemptionQR(Request $request)
    {
        $qrcode = $request->qrText;
        $timestamp = explode('|', $request->qrText)[0];
        $idcard = explode('|', $request->qrText)[1];
        $user = $this->model->where('idcard', $idcard)->first();

        QRCode::create([
            'qr_code' => $qrcode,
            'timestamp' => $timestamp,
            'user_id' => $user->id
        ]);

        return sendResponse(null, 200,"Store QR Code successfully");
    }

    public function storeCouponQR(Request $request)
    {

        info($request->qrText);

        $qrcode = $request->qrText;
        $timestamp = explode(',', $request->qrText)[0];
        $idcard = explode(',', $request->qrText)[1];
        $user = $this->model->where('idcard', $idcard)->first();

        QRCode::create([
            'qr_code' => $qrcode,
            'timestamp' => $timestamp,
            'user_id' => $user->id
        ]);

        return sendResponse(null, 200,"Store QR Code successfully");
    }

    public function checkHashValue($jsonString, $hashValue)
    {
        $secret_key = env('SECRET_KEY');

        $payload = $jsonString . $secret_key;

        $calculatedHash = hash('sha256', $payload);

        // Compare
        if (! hash_equals($calculatedHash, $hashValue)) {
            return false;
        }

        return true;
    }

    public function validatePointRedemptionQR(Request $request)
    {

        $qrCode = $request->qrCode;
        $idcard = explode('|', $qrCode)[1];
        info($idcard);
        $user = $this->model->where('idcard', $idcard)->first();
        if(!$user){
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Member Card No is invalid',
            ]);
        }
        $timestamp = $request->timestamp;

        // Build exact string that generator used
        $jsonString = json_encode($request->except('hashValue'));

        if (!$this->checkHashValue($jsonString, $request->hashValue)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Hash value is invalid'
            ]);
        }

        if (strlen((string) $timestamp) === 10) {
            $timestamp = $timestamp * 1000;
        }

        $timestamp = Carbon::createFromTimestampMs((int) $timestamp);
        $expired_time = $timestamp->addMinutes(5);

        if (Carbon::now()->gt($expired_time)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'QR Code is expired',
                'memberCardNo' => $user->idcard,
                'phoneNo' => $user->phone,
            ]);
        }

        $qrcode = QRCode::where('qr_code', $qrCode)
            ->where('user_id', $user->id)
            ->where('timestamp', $request->timestamp)
            ->first();

        if (!$qrcode) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'QR Code is invalid',
                'memberCardNo' => $user->idcard,
                'phoneNo' => $user->phone,
            ]);
        }

        return response()->json([
            'responseCode' => '1',
            'responseMessage' => 'QR Code is valid',
            'memberCardNo' => $user->idcard,
            'phoneNo' => $user->phone,
        ]);
    }

    public function validateCouponQR(Request $request)
    {
        $qrCode = $request->qrCode;
        $idcard = explode(',', $qrCode)[1];
        $user = $this->model->where('idcard', $idcard)->first();
        if(!$user){
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Member Card No is invalid',
            ]);
        }
        $timestamp = $request->timestamp;

        // Build exact string that generator used
        $jsonString = json_encode($request->except('hashValue'));

        if (!$this->checkHashValue($jsonString, $request->hashValue)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Hash value is invalid'
            ]);
        }

        if (strlen((string) $timestamp) === 10) {
            $timestamp = $timestamp * 1000;
        }

        $timestamp = Carbon::createFromTimestampMs((int) $timestamp);
        $expired_time = $timestamp->addMinutes(5);

        if (Carbon::now()->gt($expired_time)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'QR Code is expired',
                'memberCardNo' => $user->idcard,
                'phoneNo' => $user->phone,
            ]);
        }

        $qrcode = QRCode::where('qr_code', $qrCode)
            ->where('user_id', $user->id)
            ->where('timestamp', $request->timestamp)
            ->first();

        if (!$qrcode) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'QR Code is invalid',
                'memberCardNo' => $user->idcard,
                'phoneNo' => $user->phone,
            ]);
        }

        return response()->json([
            'responseCode' => '1',
            'responseMessage' => 'QR Code is valid',
            'memberCardNo' => $user->idcard,
            'phoneNo' => $user->phone,
        ]);
    }

    public function sendReceivePointNotification(Request $request)
    {
        $user = $this->model->where('idcard', $request->memberCardNo)->first();
        if (!$user) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Member Card No is invalid'
            ]);
        }

        $jsonString = json_encode($request->except('hashValue'));

        if (!$this->checkHashValue($jsonString, $request->hashValue)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Hash value is invalid'
            ]);
        }

        $noti_title = "Point Received";
        $noti_message = "Congratulations! You have received " . $request->receivePoint;

        DB::beginTransaction();
        try {

            $notification = Notification::create([
                'title' => $noti_title,
                'message' => $noti_message,
                'recipient' => 'specific'
            ]);

            UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);

            DB::commit();
            sendPushNotification($user->expo_push_token, $noti_title, $noti_message);
            return response()->json([
                "responseCode" => "1",
                "responseMessage" => "Notification is sent successfully"
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "responseCode" => "0",
                "responseMessage" => "Failed to send notification"
            ]);
        }
    }

    public function sendClaimPointNotification(Request $request)
    {

        $user = $this->model->where('idcard', $request->memberCardNo)->first();
        if (!$user) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Member Card No is invalid'
            ]);
        }


        $jsonString = json_encode($request->except('hashValue'));

        if (!$this->checkHashValue($jsonString, $request->hashValue)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Hash value is invalid'
            ]);
        }

        $noti_title = "Point Claimed";
        $noti_message = "Congratulations! You have claimed " . $request->claimPoint;

        DB::beginTransaction();
        try {

            $notification = Notification::create([
                'title' => $noti_title,
                'message' => $noti_message,
                'recipient' => 'specific'
            ]);

            UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);

            DB::commit();
            sendPushNotification($user->expo_push_token, $noti_title, $noti_message);
            return response()->json([
                "responseCode" => "1",
                "responseMessage" => "Notification is sent successfully"
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "responseCode" => "0",
                "responseMessage" => "Failed to send notification"
            ]);
        }
    }

    public function sendTransferPointNotification(Request $request)
    {

        $transferer = $this->model->where('idcard', $request->senderMemberCardNo)->first();
        $receiver = $this->model->where('idcard', $request->receiverMemberCardNo)->first();

        if (!$transferer) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Sender Member Card No is invalid'
            ]);
        }

        if (!$receiver) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Receiver Member Card No is invalid'
            ]);
        }

        $jsonString = json_encode($request->except('hashValue'));

        if (!$this->checkHashValue($jsonString, $request->hashValue)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Hash value is invalid'
            ]);
        }

        $noti_title_for_transferer = "Point Transfer";
        $noti_message_for_transferer = "Congratulations! You have transferred " . $request->transferPoint . " points to " . $receiver->name;

        $noti_title_for_receiver = "Point Received";
        $noti_message_for_receiver = "Congratulations! You have received " . $request->transferPoint . " points from " . $transferer->name;

        DB::beginTransaction();
        try {

            $notification_for_transferer = Notification::create([
                'title' => $noti_title_for_transferer,
                'message' => $noti_message_for_transferer,
                'recipient' => 'specific'
            ]);

            UserNotification::create([
                'user_id' => $transferer->id,
                'notification_id' => $notification_for_transferer->id,
            ]);

            $notification_for_receiver = Notification::create([
                'title' => $noti_title_for_receiver,
                'message' => $noti_message_for_receiver,
                'recipient' => 'specific'
            ]);

            UserNotification::create([
                'user_id' => $receiver->id,
                'notification_id' => $notification_for_receiver->id,
            ]);

            DB::commit();
            sendPushNotification($transferer->expo_push_token, $noti_title_for_transferer, $noti_message_for_transferer);
            sendPushNotification($receiver->expo_push_token, $noti_title_for_receiver, $noti_message_for_receiver);
            return response()->json([
                "responseCode" => "1",
                "responseMessage" => "Notification is sent successfully"
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "responseCode" => "0",
                "responseMessage" => "Failed to send notification"
            ]);
        }
    }

    public function sendUsePointNotification(Request $request)
    {

        $user = $this->model->where('idcard', $request->memberCardNo)->first();
        if (!$user) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Member Card No is invalid'
            ]);
        }


        $jsonString = json_encode($request->except('hashValue'));

        if (!$this->checkHashValue($jsonString, $request->hashValue)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Hash value is invalid'
            ]);
        }

        $noti_title = "Point Used";
        $noti_message = "You have used " . $request->usePoint . " points to redeem " . $request->promotionName;

        DB::beginTransaction();
        try {

            $notification = Notification::create([
                'title' => $noti_title,
                'message' => $noti_message,
                'recipient' => 'specific'
            ]);

            UserNotification::create([
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);

            DB::commit();
            sendPushNotification($user->expo_push_token, $noti_title, $noti_message);
            return response()->json([
                "responseCode" => "1",
                "responseMessage" => "Notification is sent successfully"
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "responseCode" => "0",
                "responseMessage" => "Failed to send notification"
            ]);
        }
    }

    public function getNotiUsersByBranch($branch_code)
    {

        $tokens_to_send = [];
        $user_ids = [];

        $users = $this->model->where('branch_code', $branch_code)->where('expo_push_token', '!=', null)->get();

        foreach ($users as $user) {

            if ($user) {
                array_push($tokens_to_send, $user->expo_push_token);
                array_push($user_ids, $user->id);
            }
        }

        return [
            'tokens_to_send' => $tokens_to_send,
            'user_ids' => $user_ids
        ];
    }

    public function sendNewPointRedemptionProgramNotification(Request $request)
    {

        $jsonString = json_encode($request->except('hashValue'));

        if (!$this->checkHashValue($jsonString, $request->hashValue)) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Hash value is invalid'
            ]);
        }

        $pos_db = getPosDBConnectionByBranchCode('MM-101');

        $promotion = $pos_db->table('gold_exchange.point_exchange_promotion')->where('point_exchange_promotion_pro_no', $request->promotionRefNo)->first();
        if (!$promotion) {
            return response()->json([
                'responseCode' => '0',
                'responseMessage' => 'Promotion Ref No is invalid'
            ]);
        }

        $promotion_id = $promotion->point_exchange_promotion_id;

        $data = $this->getNotiUsersByBranch($request->branchCode);

        $tokens_to_send = $data['tokens_to_send'];
        $user_ids = $data['user_ids'];

        $noti_title = "New Point Redemption Program";
        $noti_message = $request->promotionName . " is now available!";

        DB::beginTransaction();
        try {

            $notification = Notification::create([
                'title' => $noti_title,
                'message' => $noti_message,
                'recipient' => 'all',
                'route_to' => 'promotion_id:' . $promotion_id
            ]);

            foreach ($user_ids as $user_id) {
                UserNotification::create([
                    'user_id' => $user_id,
                    'notification_id' => $notification->id,
                ]);
            }

            DB::commit();
            sendPushNotification($tokens_to_send, $noti_title, $noti_message);
            return response()->json([
                "responseCode" => "1",
                "responseMessage" => "Notification is sent successfully"
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                "responseCode" => "0",
                "responseMessage" => "Failed to send notification"
            ]);
        }
    }

    public function setPushToken(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'idcard' => 'required'
        ]);

        $this->model->where('idcard', $request->idcard)->update([
            'expo_push_token' => $request->token
        ]);
    }

    public function logout(Request $request)
    {
        $this->model->where('idcard', $request->idcard)->update([
            'expo_push_token' => null,
            'device_id' => null,
            'device_name' => null
        ]);

        return sendResponse(null, 200, "Logout successfully");
    }
}
