<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('sendResponse')) {
    function sendResponse($data, $status, $message = "No message")
    {
        return response()->json([
            'data' => $data,
            'status' => $status,
            'message' => $message
        ]);
    }
}

if (!function_exists('getPosDBConnectionByBranchCode')) {
    function getPosDBConnectionByBranchCode($branch_code)
    {
        switch ($branch_code) {
            case 'MM-001':
                $conn = DB::connection('master_product');
                break;
            case 'MM-101':
                $conn = DB::connection('pos101_pgsql');
                break;
            case 'MM-102':
                $conn = DB::connection('pos102_pgsql');
                break;
            case 'MM-103':
                $conn = DB::connection('pos103_pgsql');
                break;
            case 'MM-104':
                $conn = DB::connection('pos104_pgsql');
                break;
            case 'MM-105':
                $conn = DB::connection('pos105_pgsql');
                break;
            case 'MM-106':
                $conn = DB::connection('pos106_pgsql');
                break;
            case 'MM-107':
                $conn = DB::connection('pos107_pgsql');
                break;
            case 'MM-108':
                $conn = DB::connection('pos108_pgsql');
                break;
            case 'MM-109':
                $conn = DB::connection('pos109_pgsql');
                break;
            case 'MM-110':
                $conn = DB::connection('pos110_pgsql');
                break;
            case 'MM-112':
                $conn = DB::connection('pos112_pgsql');
                break;
            case 'MM-113':
                $conn = DB::connection('pos113_pgsql');
                break;
            case 'MM-114':
                $conn = DB::connection('pos114_pgsql');
                break;
            case 'MM-115':
                $conn = DB::connection('pos115_pgsql');
                break;
            case 'MM-201':
                $conn = DB::connection('pos201_pgsql');
                break;
            case 'MM-203':
                $conn = DB::connection('pos203_pgsql');
                break;
            case 'MM-205':
                $conn = DB::connection('pos205_pgsql');
                break;
            case 'MM-504':
                $conn = DB::connection('pos504_pgsql');
                break;
            case 'MM-505':
                $conn = DB::connection('pos505_pgsql');
                break;
            case 'MM-509':
                $conn = DB::connection('pos509_pgsql');
                break;
            case 'MM-510':
                $conn = DB::connection('pos510_pgsql');
                break;
            case 'MM-511':
                $conn = DB::connection('pos511_pgsql');
                break;
        }
        return $conn;
    }

}
