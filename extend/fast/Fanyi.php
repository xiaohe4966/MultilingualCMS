<?php
/*
 * @Author: he4966
 */
/*
 * @Author: he4966
 */

namespace fast;
use think\Config;

/**
 * Fanyi 翻译类
 */
class Fanyi
{

    /**
     * 翻译
     * @param string $query 查询内容
     * @param string $from 自动识别
     * @param string $to 翻译语言
     * @return mixed|string
     */
    public static function fanyi($query, $from = 'auto', $to = 'zh')
    {
        switch (Config::get('site.fanyi_app')) {
            case 'baidu':
                $content = (new BaiduTranslate())->translate($query, $from, $to);
                break;
            
            default:
                $content = (new BaiduTranslate())->translate($query, $from, $to);
                break;
        }
        return $content;
    }
}