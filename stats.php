#!/usr/bin/env php
<?php
use voku\db\DB;

error_reporting(E_ERROR);
ini_set('display_errors', true);

//自动加载
$loader = require('vendor/autoload.php');

/**
 * 生成随机码
 */
function random($min = 0, $max = 1)
{
    return $min + mt_rand()/mt_getrandmax()*($max-$min);
}

/**
 * 获取CEO.BI站点交易数据
 */
function get_ceobi_data()
{
    $result = [];
    $ceobiUri = 'https://ceo.bi/ajax/getMarketPrice/';
    $client = new \GuzzleHttp\Client();
    $response = $client->request('GET', $ceobiUri.'t='.random());
    $code = $response->getStatusCode();
    $response = $response->getBody();

    if ($code==200 && !empty($response)) {
        $result = json_decode($response, true);
        $result = isset($result['qc']) ? $result['qc']:[];
    }

    return $result;
}

/**
 * 按成交量排序(CEO平台币种)
 */
function sort_by_subtotal($data)
{
    $result = [];

    if (!empty($data)) {
        foreach ($data as $item) {
            $id = $item['id'];
            $newPrice = $item['new_price']; //当前价格
            $volume = $item['volume']; //成交数量(24小时)
            $subTotal = floor($newPrice*$volume);
            $result[$id] = $subTotal;
        }

        //按成交金额排序
        arsort($result);
    }

    return $result;
}


try {
    //请求CEO.BI站点数据
    $result = get_ceobi_data();

    if (!empty($result)) {
        $total = 0; //总交易额
        $totalFee = 0; //总手续费
        $coinList = []; //币种列表
        $i = 1;

        //平台所有币种列表
        foreach ($result as $item) {
            $id = $item['id'];
            $coinList[$id] = $item;
        }

        //按成交金额排序(倒序)
        $sortBySubTotal = sort_by_subtotal($result);

        foreach ($sortBySubTotal as $id => $subTotal) {
            $coin = $coinList[$id];
            $title = $coin['title'];
            $name = $coin['name'];
            $newPrice = $coin['new_price'];
            $change = $coin['change'];
            $total += $subTotal;

            if (!in_array($name, ['ceo_qc','coo_qc'])) {
                $totalFee += $subTotal*0.004;
            }

            if (in_array($id, [8,65]) || $subTotal > 15000000) { //成交额大于1500W
                echo "{$i}, {$title}, {$newPrice}, {$change}, {$subTotal}\n";
                $i++;
            }
        }

        $rate = number_format($totalFee*0.6/9598282.9, 2);
        echo "ALL: {$total}\n";
        echo "RATE: {$rate} (0.38=>69)\n";
    }
} catch (Exception $e) {
	print_r($e);
}
