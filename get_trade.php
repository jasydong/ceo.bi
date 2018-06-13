<?php
use voku\db\DB;

error_reporting(E_ERROR);
ini_set('display_errors', true);

//自动加载
$loader = require('vendor/autoload.php');

try {
    //获取统计数据
    $result = ["data"=>[]];
    $db = DB::getInstance('localhost', 'root', 'admin2011', 'ceobi');
    $query = $db->query("select trade_total, create_time from trade_realtime order by id desc limit 10000;");
    $data = $query->fetchAllArray();

    if (!empty($data)) {
        foreach ($data as $item) {
            $tradeTotal = number_format($item['trade_total']/100000000, 2);
            $createTime = intval($item['create_time']);
            $result["data"][] = [$createTime, floatval($tradeTotal)];
        }
    }

    echo json_encode($result);
} catch (Exception $e) {
	print_r($e);
}
