<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\UsedCouponLog;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    protected $user;

    public function __construct($resource, $user = null)
    {
        parent::__construct($resource);
        $this->user = $user;
    }

    public function toArray(Request $request): array
    {

        $user = $this->user?->toArray();

        $data = parent::toArray($request);

        $coupon_id = $data['coup_id'] ?? $data['coupon_id'];
        $expiry_date = $data['date_expire'] ?? $data['expiry_date'];

        if (isset($data['coup_type_id'])) {
            if ($data['coup_type_id'] != 1 && $data['coup_type_id'] != 6) {
                if ($data['one_status'] === 'Y' && UsedCouponLog::where('user_id', $user['id'])->where('coupon_id', $coupon_id)->doesntExist()) {
                    UsedCouponLog::create([
                        'user_id' => $user['id'],
                        'coupon_id' => $coupon_id,
                        'points' => $data['coup_value'],
                        'coupon_type' => $data['coup_type_id']
                    ]);
                }
            }
        }

        $used_coupon = UsedCouponLog::where('user_id', $user['id'])->where('coupon_id', $coupon_id)->first();

        $data['status'] =  $used_coupon ? "used" : "selected";

        if ($data['status'] == "selected") {
            $data['status'] = Carbon::parse($expiry_date)->isPast() ? "expired" : "selected";
        }

        if($data['status'] == "used"){
            $data['used_at'] = $used_coupon->created_at;
        }

        return $data;
    }
}
