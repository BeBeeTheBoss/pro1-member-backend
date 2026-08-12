<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PointTransferController extends Controller
{
    public function transfer(Request $request)
    {
        $idcard = $request->idcard;
        $points = $request->amount;

        $cloud_db = DB::connection('Cloud');

        $remaining = $points;

        $scores = $cloud_db->table('imember.imember_score')
            ->where('idcard', $idcard)
            ->where('score_balance', '>', 0)
            ->orderBy('date_now', 'asc')
            ->get();

        $balance_points = $scores->sum('score_balance');

        if ($balance_points < $points) {
            return sendResponse(null, 405, "Insufficient points");
        }

        $sender = $cloud_db->table('public.gbh_customer')
            ->where('identification_card', $idcard)
            ->first();

        $receiver = $cloud_db->table('public.gbh_customer')
            ->where('mobile', $request->phone)
            ->first();

        if(!$receiver) {
            return sendResponse(null, 404, "Receiver not found");
        }

        $cloud_db->beginTransaction();
        try {
            foreach ($scores as $score) {
                if ($remaining <= 0)
                    break;

                $deduct = min($score->score_balance, $remaining);

                $cloud_db->table('imember.imember_score')
                    ->where('imember_score_id', $score->imember_score_id)
                    ->update([
                        'score_balance' => DB::raw("score_balance - $deduct")
                    ]);

                $score_data = $cloud_db->table('imember.imember_score')
                    ->where('imember_score_id', $score->imember_score_id)
                    ->first();

                $transfer_date = Carbon::now()->format('Y-m-d H:i:s.u');
                $transfer_doc = 'TO' . str_replace(['-', ':', '.', ' '], '', Carbon::now()->format('y-m-d h:i:s.u'));

                $cloud_db->table('imember.transfer_point')->insert([
                    'transfer_doc' => $transfer_doc,
                    'transfer_date' => $transfer_date,
                    'from_member' => $sender->identification_card,
                    'to_phone' => $receiver->mobile,
                    'to_member' => $receiver->identification_card,
                    'point' => $deduct,
                    'msg_member' => $request->message,
                    'transfer_actvice' => true,
                    'receivename' => $receiver->fullname
                ]);

                $ref_doc = 'TNF' . str_replace(['-', ':', '.', ' '], '', Carbon::now()->format('y-m-d h:i:s.u'));

                $cloud_db->table('imember.log_transfer_point')->insert([
                    'log_transfer_doc' => $transfer_doc,
                    'log_imember_doc' => $ref_doc,
                    'point' => $deduct,
                    'date_expired' => $score_data->date_expire,
                    'log_date' => $transfer_date
                ]);

                $log_transfer_point = $cloud_db->table('imember.log_transfer_point')
                    ->where('log_transfer_doc', $transfer_doc)
                    ->where('log_imember_doc', $ref_doc)
                    ->first();

                $cloud_db->table('imember.imember_score')->insert([
                    'gbh_customer_id' => $receiver->gbh_customer_id,
                    'ref_no' => $ref_doc,
                    'ref_id' => $log_transfer_point->log_id,
                    'score_flag' => 0,
                    'score' => 0,
                    'score_net' => $deduct,
                    'customer_barcode' =>  $receiver->customer_barcode,
                    'date_now' => Carbon::now(),
                    'branch_code' => $receiver->branch_code,
                    'idcard' => $receiver->identification_card,
                    'score_balance' => $deduct,
                    'date_expire' => $score_data->date_expire,
                    'customer_rank_id' => $receiver->customer_rank_id
                ]);

                $remaining -= $deduct;
            }

            $cloud_db->commit();
            return sendResponse(null, 200, "Points transferred successfully");
        } catch (\Exception $e) {
            info("Error: " . $e->getMessage());

            $cloud_db->rollBack();
            return sendResponse(null, 500, "Something went wrong, Try again");
        }
    }
}
