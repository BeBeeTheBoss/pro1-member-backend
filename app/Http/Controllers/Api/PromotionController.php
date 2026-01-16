<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {

        $pos_db = getPosDBConnectionByBranchCode('MM-101');

        $promotions = $pos_db->table('gold_exchange.point_exchange_promotion')
            ->whereRaw('? BETWEEN point_exchange_promotion_datestart AND point_exchange_promotion_dateend', [now()])
            ->get();


        return sendResponse($promotions->toArray(), 200);
    }

    public function show($id){

        info($id);

        $pos_db = getPosDBConnectionByBranchCode('MM-101');

        $promotion = $pos_db->table('gold_exchange.point_exchange_promotion_item')
            ->where('point_exchange_promotion_id', $id)
            ->get();

        return sendResponse($promotion->toArray(), 200);



    }

}
