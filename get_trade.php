<?php
use voku\db\DB;

error_reporting(E_ERROR);
ini_set('display_errors', true);

//自动加载
$loader = require('vendor/autoload.php');

try {
    //获取统计数据
    $result = ["code"=>200, "data"=>[]];
    $db = DB::getInstance('localhost', 'root', 'admin2011', 'ceobi');
    $query = $db->query("select trade_total, create_time from trade_realtime order by id asc limit 10000;");
    $data = $query->fetchAllArray();

    if (!empty($data)) {
        foreach ($data as $item) {
            $tradeTotal = number_format($item['trade_total']/100000000, 2);
            $createTime = $item['create_time']*1000;
            $result["data"][] = [$createTime, floatval($tradeTotal)];
        }
    }

    echo json_encode($result);
} catch (Exception $e) {
	print_r($e);
}
