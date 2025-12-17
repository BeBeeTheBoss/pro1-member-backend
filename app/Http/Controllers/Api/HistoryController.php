<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{

    public function index($idcard)
    {
        $cloud_db = DB::connection('Cloud');

        info("IDCard :" . $idcard);

        $sql = "SELECT
            EXTRACT(YEAR FROM subqueryalias.date)::int AS year,
            EXTRACT(MONTH FROM subqueryalias.date)::int AS month,
            SUM(CASE WHEN subqueryalias.point > 0 THEN subqueryalias.point ELSE 0 END)::numeric(19,0) AS total_received_points,
            SUM(CASE WHEN subqueryalias.point < 0 THEN subqueryalias.point ELSE 0 END)::numeric(19,0) AS total_redeemed_points
        FROM (
            -- 1. Receive
            SELECT
                'Receive'::text AS type,
                aa.branch_code,
                cc.branch_name,
                aa.customer_barcode AS custcode,
                aa.idcard AS memberid,
                aa.date_now AS date,
                aa.ref_no AS docno,
                aa.score_net::numeric(19,0) AS point
            FROM imember.imember_score aa
            JOIN gbh_customer bb ON aa.customer_barcode::text = bb.customer_barcode::text
            JOIN master_branch cc ON aa.branch_code::text = cc.branch_code::text
            WHERE substring(aa.ref_no::text, 1, 3) <> ALL (ARRAY['TNF', 'ADJ'])
            AND aa.idcard = '$idcard'

            UNION ALL
            -- 2. Expired
            SELECT
                'Expired'::text AS type,
                aa.branch_code,
                bb.branch_name,
                aa.customer_barcode AS custcode,
                aa.idcard AS memberid,
                aa.date_expire::date AS date,
                aa.ref_no AS docno,
                (-1 * aa.score_balance)::numeric(19,0) AS point
            FROM imember.imember_score aa
            JOIN master_branch bb ON aa.branch_code::text = bb.branch_code::text
            WHERE aa.score_balance <> 0::numeric
            AND aa.idcard = '$idcard'
            AND aa.date_expire::date < NOW()::date

            UNION ALL
            -- 3. Redempt
            SELECT
                'Redempt'::text AS type,
                pay.branchcode AS branch_code,
                pay.branchname AS branch_name,
                pay.cuscode AS custcode,
                pay.identification AS memberid,
                pay.date_now AS date,
                pay.promotion_name AS docno,
                (-1 * pay.score_pay)::numeric(19,0) AS point
            FROM imember_pay.imember_score_pay pay
            WHERE pay.identification = '$idcard'

            UNION ALL
            -- 4. Adjust Plus
            SELECT
                'Adjustplus'::text AS type,
                aa.branch_code,
                bb.branch_name,
                aa.p_cus_barcode AS custcode,
                aa.p_cus_id AS memberid,
                aa.p_date AS date,
                aa.p_doc AS docno,
                aa.p_point_adj::numeric(19,0) AS point
            FROM member2016.point_adjment aa
            JOIN master_branch bb ON aa.branch_code::text = bb.branch_code::text
            WHERE aa.statusf = 'Y'
            AND aa.type_doc = 'P'
            AND aa.p_cus_id = '$idcard'

            UNION ALL
            -- 5. Adjust Minus
            SELECT
                'Adjustminus'::text AS type,
                aa.branch_code,
                bb.branch_name,
                aa.p_cus_barcode AS custcode,
                aa.p_cus_id AS memberid,
                aa.p_date AS date,
                aa.p_doc AS docno,
                (-1 * aa.p_point_adj)::numeric(19,0) AS point
            FROM member2016.point_adjment aa
            JOIN master_branch bb ON aa.branch_code::text = bb.branch_code::text
            WHERE aa.statusf = 'Y'
            AND aa.type_doc = 'D'
            AND aa.p_cus_id = '$idcard'

            UNION ALL
            -- 6. Transfer Out
            SELECT
                'TransferOut'::text AS type,
                bb.branch_code,
                cc.branch_name,
                bb.customer_barcode AS custcode,
                aa.from_member AS memberid,
                aa.transfer_date::date AS date,
                aa.transfer_doc AS docno,
                (-1 * aa.point)::numeric(19,0) AS point
            FROM imember.transfer_point aa
            JOIN gbh_customer bb ON aa.from_member::text = bb.identification_card::text
            JOIN master_branch cc ON bb.branch_code::text = cc.branch_code::text
            WHERE aa.from_member = '$idcard'

            UNION ALL
            -- 7. Transfer In
            SELECT
                'TransferIn'::text AS type,
                aa.branch_code,
                bb.branch_name,
                aa.customer_barcode AS custcode,
                aa.idcard AS memberid,
                aa.date_now AS date,
                aa.ref_no AS docno,
                aa.score_net::numeric(19,0) AS point
            FROM imember.imember_score aa
            JOIN master_branch bb ON aa.branch_code::text = bb.branch_code::text
            WHERE substring(aa.ref_no::text, 1, 3) = 'TNF'
            AND aa.idcard = '$idcard'
        ) subqueryalias
        GROUP BY EXTRACT(YEAR FROM subqueryalias.date), EXTRACT(MONTH FROM subqueryalias.date)
        ORDER BY year, month;

        ";

        // AND aa.date_now::date BETWEEN '$startDate'::date AND '$endDate'::date
        // AND pay.date_now::date BETWEEN '$startDate'::date AND '$endDate'::date

        $summary = $cloud_db->select($sql);
        info($summary);

        return sendResponse($summary, 200);
    }


    public function show($idcard, $month)
    {

        info("IDCard :" . $idcard);
        info("Month :" . $month);
        $cloud_db = DB::connection('Cloud');

        $idcard = $idcard;
        $startDate = $month . "-01";

        if (in_array(explode("-", $month)[1], [4, 6, 9, 11])) {
            $endDate = $month . "-30";
        }else{
            $endDate = $month . "-31";
        }

        $sql = "SELECT
                ROW_NUMBER() OVER (ORDER BY subqueryalias.date DESC) AS listno,
                subqueryalias.type,
                subqueryalias.branch_code,
                subqueryalias.branch_name,
                subqueryalias.custcode,
                subqueryalias.memberid,
        subqueryalias.to_member,
                subqueryalias.date,
                subqueryalias.docno,
                subqueryalias.point,
        subqueryalias.sender_phone,
        subqueryalias.receiver_phone
        FROM (
            -- 1. Receive
            SELECT
                'Receive'::text AS type,
                aa.branch_code,
                cc.branch_name,
                aa.customer_barcode AS custcode,
                aa.idcard AS memberid,
				'-' as to_member,
                aa.date_now AS date,
                aa.ref_no AS docno,
                aa.score_net::numeric(19,0) AS point,
				'-' as sender_phone,
				'-' as receiver_phone
            FROM imember.imember_score aa
            JOIN gbh_customer bb
                ON aa.customer_barcode::text = bb.customer_barcode::text
            JOIN master_branch cc
                ON aa.branch_code::text = cc.branch_code::text
            WHERE substring(aa.ref_no::text, 1, 3) <> ALL (ARRAY['TNF', 'ADJ'])
            AND aa.idcard = '$idcard'
            AND aa.date_now::date BETWEEN '$startDate' and '$endDate'

            UNION ALL

            -- 2. Expired
            SELECT
                'Expired'::text AS type,
                aa.branch_code,
                bb.branch_name,
                aa.customer_barcode AS custcode,
                aa.idcard AS memberid,
				'-' as to_member,
                aa.date_expire AS date,
                aa.ref_no AS docno,
                (-1 * aa.score_balance)::numeric(19,0) AS point,
				'-' as sender_phone,
				'-' as receiver_phone
            FROM imember.imember_score aa
            JOIN master_branch bb
                ON aa.branch_code::text = bb.branch_code::text
            WHERE aa.score_balance <> 0::numeric
            AND aa.idcard = '$idcard'
            AND aa.date_expire::date < NOW()::date

            UNION ALL

            -- 3. Redempt
            SELECT
                'Redempt'::text AS type,
                pay.branchcode AS branch_code,
                pay.branchname AS branch_name,
                pay.cuscode AS custcode,
                pay.identification AS memberid,
				'-' as to_member,
                pay.date_now AS date,
                pay.promotion_name AS docno,
                (-1 * pay.score_pay)::numeric(19,0) AS point,
				'-' as sender_phone,
				'-' as receiver_phone
            FROM imember_pay.imember_score_pay pay
            WHERE pay.identification = '$idcard'
            AND pay.date_now::date BETWEEN '$startDate' and '$endDate'

            UNION ALL

            -- 4. Adjust Plus
            SELECT
                'Adjustplus'::text AS type,
                aa.branch_code,
                bb.branch_name,
                aa.p_cus_barcode AS custcode,
                aa.p_cus_id AS memberid,
				'-' as to_member,
                aa.p_date AS date,
                aa.p_doc AS docno,
                aa.p_point_adj::numeric(19,0) AS point,
				'-' as sender_phone,
				'-' as receiver_phone
            FROM member2016.point_adjment aa
            JOIN master_branch bb
                ON aa.branch_code::text = bb.branch_code::text
            WHERE aa.statusf = 'Y'
            AND aa.type_doc = 'P'
            AND aa.p_cus_id = '$idcard'
            AND aa.p_date::date BETWEEN '$startDate' and '$endDate'

            UNION ALL

            -- 5. Adjust Minus
            SELECT
                'Adjustminus'::text AS type,
                aa.branch_code,
                bb.branch_name,
                aa.p_cus_barcode AS custcode,
                aa.p_cus_id AS memberid,
				'-' as to_member,
                aa.p_date AS date,
                aa.p_doc AS docno,
                (-1 * aa.p_point_adj)::numeric(19,0) AS point,
				'-' as sender_phone,
				'-' as receiver_phone
            FROM member2016.point_adjment aa
            JOIN master_branch bb
                ON aa.branch_code::text = bb.branch_code::text
            WHERE aa.statusf = 'Y'
            AND aa.type_doc = 'D'
            AND aa.p_cus_id = '$idcard'
            AND aa.p_date::date BETWEEN '$startDate' and '$endDate'

            UNION ALL

            -- 6. Transfer Out
            SELECT
                'TransferOut'::text AS type,
                bb.branch_code,
                cc.branch_name,
                bb.customer_barcode AS custcode,
                aa.from_member AS memberid,
        		to_member, ---'' as to_member
                aa.transfer_date AS date,
                aa.transfer_doc AS docno,
                (-1 * aa.point)::numeric(19,0) AS point,
        (select mobile as sender_phone from gbh_customer where customer_barcode= bb.customer_barcode), ---'' as sender_phone
        to_phone as receiver_phone  ---'' as receiver_phone
      FROM imember.transfer_point aa
            JOIN gbh_customer bb
                ON aa.from_member::text = bb.identification_card::text
            JOIN master_branch cc
                ON bb.branch_code::text = cc.branch_code::text
            WHERE aa.from_member = '$idcard'
            AND aa.transfer_date::date BETWEEN '$startDate' and '$endDate'

            UNION ALL

            -- 7. Transfer In
           SELECT
                'TransferIn'::text AS type,
                bb.branch_code,
                cc.branch_name,
                bb.customer_barcode AS custcode,
                aa.from_member AS memberid,
        		to_member, ---'' as to_member
                aa.transfer_date AS date,
                aa.transfer_doc AS docno,
                aa.point::numeric(19,0) AS point,
        (select mobile as sender_phone from gbh_customer where customer_barcode= bb.customer_barcode), ---'' as sender_phone
        to_phone as receiver_phone  ---'' as receiver_phone
      FROM imember.transfer_point aa
            JOIN gbh_customer bb
                ON aa.from_member::text = bb.identification_card::text
            JOIN master_branch cc
                ON bb.branch_code::text = cc.branch_code::text
      AND to_member='$idcard'
     AND aa.transfer_date::date BETWEEN '$startDate' and '$endDate'
        ) subqueryalias
        ORDER BY subqueryalias.date DESC";

        $histories = $cloud_db->select($sql);

        info($histories);


        return sendResponse($histories, 200);
    }
}
