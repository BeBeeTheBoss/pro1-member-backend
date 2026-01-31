<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PromotionController extends Controller
{
    public function index(Request $request)
    {

        $pos_db = getPosDBConnectionByBranchCode('MM-101');
        $cloud_db = DB::connection('Cloud');

        $promotions = $pos_db->table('gold_exchange.point_exchange_promotion')
            ->whereRaw('? BETWEEN point_exchange_promotion_datestart AND point_exchange_promotion_dateend', [now()])
            ->get();

        $coupon_promotion = $cloud_db->table('imember_pay.rate_redeem_point')->first();

        return sendResponse([
            'promotions' => $promotions,
            'coupon_promotion' => $coupon_promotion
        ], 200);
    }

    public function show(Request $request, $id)
    {

        info($id);
        info($request->all());
        $id = explode(',', $id)[0];

        $pos_db = getPosDBConnectionByBranchCode('MM-101');
        $cloud_db = DB::connection('Cloud');

        if ($request->type === 'coupon') {
            $promotion = $cloud_db->table('imember_pay.rate_redeem_point')->first();
        } else {
            $promotion = $pos_db->table('gold_exchange.point_exchange_promotion_item')
                ->where('point_exchange_promotion_no', $id)
                ->get();
        }


        return sendResponse($request->type === 'coupon' ? $promotion : $promotion->toArray(), 200);
    }
}
