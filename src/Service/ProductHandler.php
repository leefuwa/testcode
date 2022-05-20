<?php

namespace App\Service;

class ProductHandler
{
    private $products;

    public function __construct($products = [])
    {
        return $this->products  = $products;
    }

    /**
     * 获取总金额
     *
     * @return float|int
     */
    public function totalPrice()
    {
        return array_sum(array_column($this->products, 'price'));
    }

    /**
     * 将商品创建日期，转换成时间戳
     *
     * @return array
     */
    public function toUnixTimestamp()
    {
        $products = $this->products;
        array_walk($products, function(&$v) {
            $v['create_at'] = strtotime($v['create_at']) ?: 0;
        });

        return $products;
    }

    /**
     * 获取把商品以金額排序（由大至小），並 篩選商品類種是$type的商品
     *
     * @param string $type
     * @return array
     */
    public function toOrderByPrice($type = 'dessert') {
        $type = strtolower($type);

        $products = array_filter($this->products, function ($v) use ($type) {
           return strtolower($v['type']) == $type;
        });

        uasort($products, function ($a, $b) {
           return $a['price'] == $b['price'] ? 0 : ($a['price'] < $a['price'] ? 1 : -1);
        });

        return $products;
    }
}