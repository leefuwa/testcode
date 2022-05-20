<?php
namespace App\Service;

/**
 * 公用方法
 *
 *
 *
 */
class Common
{ 
    protected static $debug;

    /**
     * 问题
     * 1.应增加商户坐标的缓存，避免重复调用接口情况。
     * 2.57行判断存在问题，$response存在null的可能性，那么$coordinate==null也是调用失败
     * 3.68行中!isset($data['error'])应调整为empty($data['error']),在规范的接口中，字段是固定的。
     * 4.应增加验证经纬度方法正确才给以返回。
     * 5.獲取 Thrift 服務的结果应提取出来做成方法调用，主程方法不应有接口判断的逻辑
     *
     * geo helper 地址转换为坐标
     * @param $address
     * @return bool|string
     */
    public function geoHelperAddress($address, $merchant_id = '')
    {

        try {
            $cackeKey = 'cache-address-'.$address;

            // 從獲取座標
            $userLocation = redisx()->get($cackeKey);
            if ($userLocation) {
                return $userLocation;
            }

            $key = 'time=' . time();

            // requestLog：寫日志
            requestLog('Backend', 'Thrift', 'Http', 'phpgeohelper\\Geocoding->convert_addresses', 'https://geo-helper-hostr.ks-it.co',  [[$address, $key]]);

            // getThriftService： 獲取 Thrift 服務
            $geoHelper = ServiceContainer::getThriftService('phpgeohelper\\Geocoding');
            $param = json_encode([[$address, $key]]);

            // 調用接口，以地址獲取座標
            $response = $geoHelper->convert_addresses($param);
            $response = json_decode($response, true);

            if ($response['error'] == 0) {
                responseLog('Backend', 'phpgeohelper\\Geocoding->hksf_addresses', 'https://geo-helper-hostr.ks-it.co', '200', '0',  $response);
                $data = $response['data'][0];
                $coordinate = $data['coordinate'];

                // 如果返回 '-999,-999'，表示調用接口失敗，那麼直接使用商家位置的座標
                if ($coordinate == '-999,-999') {
                    infoLog('geoHelper->hksf_addresses change failed === ' . $address);
                    if ($merchant_id) {
                        $sMerchant = new Merchant();
                        $res = $sMerchant->get_merchant_address($merchant_id);
                        $user_location = $res['latitude'] . ',' . $res['longitude'];
                        return $user_location;
                    }
                    infoLog('geoHelper->hksf_addresses change failed === merchant_id is null' . $merchant_id);
                    return false;
                }
                if (!isset($data['error']) && (strpos($coordinate,',') !== false)) {
                    $arr = explode(',', $coordinate);
                    $user_location = $arr[1] . ',' . $arr[0];

                    // set cache
                    redisx()->set($cackeKey, $user_location);
                    return $user_location;
                }
            }
            responseLog('Backend', 'phpgeohelper\\Geocoding->hksf_addresses', 'https://geo-helper-hostr.ks-it.co', '401', '401',  $response);
            return false;
        } catch (\Throwable $t) {
            criticalLog('geoHelperAddress critical ==' . $t->getMessage());
            return 0;
        }
    }

    /**
     * 问题
     * 1.尚未判断$open_status_arr边界外的数据进行判断
     * 2.所有状态应由统一静态模块管理
     *
     * 回调状态过滤
     * @param $order_id
     * @param $status
     * @return int|string
     */
    private static $statusMap = [
        900 => true,
        909 => false,
        915 => false,
        916 => false,
        901 => 1,
        902 => 2,
        903 => 3,
    ];
    public static function checkStatusCallback($order_id, $status)
    {
        if (!array_key_exists($status, self::$statusMap)) {
            throw new \Exception($status . ' status code error');
        }
        if (self::$statusMap[$status] === true) {
            return 1;
        } else if (self::$statusMap[$status] === false) {
            infoLog('checkStatusCallback backend code is 909 915 916');
            return 0;
        } else {
            return $order_id . '-' . self::$statusMap[$status];
        }

        // 是900 可以回调
        if ($status == 900) {
            return 1;
        }
        // backend状态为 909 915 916 时 解锁工作单 但不回调
        $code_arr = ['909', '915', '916'];
        if (in_array($status, $code_arr)) {
            infoLog('checkStatusCallback backend code is 909 915 916');
            return 0;
        }

        $open_status_arr = ['901' => 1, '902' => 2, '903' => 3];
        return $order_id.'-'.$open_status_arr[$status];
    }
}
