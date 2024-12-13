<?php
/*
 * @Author: he4966
 */

namespace fast;


use think\Exception;
use think\Config;
use think\Cache;

class BaiduTranslate
{
    protected $appId;
    protected $appSecret;
    protected $apiUrl;
    protected $QPS;

    public function __construct($appId = null, $appSecret = null, $QPS = null)
    {
        $this->appId = $appId ?? Config::get('site.baidu_app_id');
        $this->appSecret = $appSecret ?? Config::get('site.baidu_app_secret');
        $this->QPS = $QPS ?? Config::get('site.baidu_app_qps');
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
        //限制速度 判断用缓存
        $cacheKey = 'baidu_translate_'.$this->QPS;
        $num = Cache::get($cacheKey);
        if ($num) {
            if($this->QPS<=$num){
                while (true) {
                    $num = Cache::get($cacheKey);
                    if(!$num){
                        break;
                    }
                    sleep(0.1);
                }
            }
        }else{
            $num = 0;
        }
        Cache::set($cacheKey, $num + 1, 1);

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
        // halt($params);

        try {
            $result = \fast\Http::post($this->apiUrl, $params);
            $result = json_decode($result, true);
            if (isset($result['trans_result'][0]['dst'])) {
                return $result['trans_result'][0]['dst'];
            } else {
                throw new Exception("翻译失败：" . $result['error_msg']);
            }
        } catch (Exception $e) {
            throw new Exception("翻译错误：" . $e->getMessage() . json_encode($params, JSON_UNESCAPED_UNICODE));
        }
    }
}
