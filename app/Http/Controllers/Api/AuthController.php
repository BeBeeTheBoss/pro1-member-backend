<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct(protected User $model)
    {
    }


    public function login(Request $request)
    {

        $cloud_db = DB::connection('Cloud');
        $member_info = $cloud_db->table(table: 'public.gbh_customer')
            ->where('mobile', $request->phone)
            ->first();

        return sendResponse($member_info, 200);
    }

    public function getPoints(Request $request)
    {

        info($request->all());
        $cloud_db = DB::connection('Cloud');

        $scores = $cloud_db->table('imember.imember_score')
            ->where('idcard', $request->idcard)
            ->where('score_balance', '>', 0)
            ->sum('score_balance');

        return sendResponse($scores, 200);
    }

}
