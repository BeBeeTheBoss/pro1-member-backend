<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UsedCouponLog;
use App\Models\SelectedCoupon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\CouponResource;
use App\Models\QRCode;
use Illuminate\Support\Facades\Storage;

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

        // if($this->model->where('idcard',$member_info->identification_card)->first()) {
        //     return sendResponse(null, 404, "Member account already exists");
        // }

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

        info($request->qrText);

        $qrcode = $request->qrText;
        $timestamp = explode('|', $request->qrText)[0];
        $user = $this->model->where('idcard', $request->idcard)->first();

        QRCode::create([
            'qr_code' => $qrcode,
            'timestamp' => $timestamp,
            'user_id' => $user->id
        ]);

        return sendResponse(null, 200);
    }

    public function validatePointRedemptionQR(Request $request)
    {
        $secret_key = env('SECRET_KEY');

        $qrCode = $request->qrCode;
        $idcard = explode('|', $qrCode)[1];
        $user = $this->model->where('idcard', $idcard)->first();
        $timestamp = $request->timestamp;

        // Build exact string that generator used
        $payload = '{"qrCode":"' . $qrCode . '","timestamp":"' . $timestamp . '"}' . $secret_key;

        // SHA-256 hash (not HMAC)
        $calculatedHash = hash('sha256', $payload);

        // Compare
        if (! hash_equals($calculatedHash, $request->hashValue)) {
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
        $secret_key = env('SECRET_KEY');

        $qrCode = $request->qrCode;
        $idcard = explode(',', $qrCode)[1];
        $user = $this->model->where('idcard', $idcard)->first();
        $timestamp = $request->timestamp;

        // Build exact string that generator used
        $payload = '{"qrCode":"' . $qrCode . '","timestamp":"' . $timestamp . '"}' . $secret_key;

        // SHA-256 hash (not HMAC)
        $calculatedHash = hash('sha256', $payload);

        // Compare
        if (! hash_equals($calculatedHash, $request->hashValue)) {
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

}
