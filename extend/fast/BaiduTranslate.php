<?php
/*
 * @Author: he4966
 */
/*
 * @Author: he4966
 */
// application/BaiduTranslate.php

namespace fast;


use think\Exception;
use think\Config;
class BaiduTranslate
{
    protected $appId;
    protected $appSecret;
    protected $apiUrl;

    public function __construct($appId=null, $appSecret=null)
    {
        $this->appId = $appId??Config::get('site.baidu_app_id');
        $this->appSecret = $appSecret??Config::get('site.baidu_app_secret');
        $this->apiUrl = "https://api.fanyi.baidu.com/api/trans/vip/translate";
    }

    /**
     * 调用百度翻译API进行翻译
     * @param string $query 要翻译的文本
     * @param string $from 源语言
     * @param string $to 目标语言
     * @return mixed
     */
    public  function translate($query, $from = 'auto', $to = 'zh')
    {
        $salt = rand(10000, 99999);
        $sign = md5($this->appId . $query . $salt . $this->appSecret);

        $params = [
            'q'     => $query,
            'from'  => $from,
            'to'    => $to,
            'appid' => $this->appId,
            'salt'  => $salt,
            'sign'  => $sign,
        ];

     
        try {
            $result = \fast\Http::post($this->apiUrl, $params);
            $result = json_decode($result, true);
            if (isset($result['trans_result'][0]['dst'])) {
                return $result['trans_result'][0]['dst'];
            } else {
                throw new Exception("翻译失败：" . $result['error_msg']);
            }
        } catch (Exception $e) {
            throw new Exception("翻译错误：" . $e->getMessage());
        }
    }
}