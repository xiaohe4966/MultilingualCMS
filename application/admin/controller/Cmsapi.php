<?php
/*
 * @Author: he4966
 */

namespace app\admin\controller;

use app\common\controller\Backend;
use app\common\exception\UploadException;
use app\common\library\Upload;
use fast\Random;
use think\addons\Service;
use think\Cache;
use think\Config;
use think\Db;
use think\Lang;
use think\Loader;
use think\Response;
use think\Validate;

/**
 * Cmsapi异步请求接口
 * @internal
 */
class Cmsapi extends Backend
{

    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包
     */
    public function lang()
    {
        $this->request->get(['callback' => 'define']);
        $header = ['Content-Type' => 'application/javascript'];
        if (!config('app_debug')) {
            $offset = 30 * 60 * 60 * 24; // 缓存一个月
            $header['Cache-Control'] = 'public';
            $header['Pragma'] = 'cache';
            $header['Expires'] = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        }

        $controllername = $this->request->get('controllername');
        $lang = $this->request->get('lang');
        if (!$lang || !in_array($lang, config('allow_lang_list')) || !$controllername || !preg_match("/^[a-z0-9_\.]+$/i", $controllername)) {
            return jsonp(['errmsg' => '参数错误'], 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
        }

        $controllername = input("controllername");
        $className = Loader::parseClass($this->request->module(), 'controller', $controllername, false);

        //存在对应的类才加载
        if (class_exists($className)) {
            $this->loadlang($controllername);
        }

        return jsonp(Lang::get(), 200, $header, ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }


    public function upload()
    {
    }

}
