<?php

// 公共助手函数

use think\exception\HttpResponseException;
use think\Response;
use think\Cache;
use think\Config;
use think\Db;

if (!function_exists('__')) {

    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param string | array  $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || !$name) {
            return $name;
        }
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param int    $size      大小
     * @param string $delimiter 分隔符
     * @param int    $precision 小数位数
     * @return string
     */
    function format_bytes($size, $delimiter = '', $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 5; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . $delimiter . $units[$i];
    }
}

if (!function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间
     * @param int    $time   时间戳
     * @param string $format 日期时间格式
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);
        return date($format, $time);
    }
}

if (!function_exists('human_date')) {

    /**
     * 获取语义化时间
     * @param int $time  时间
     * @param int $local 本地时间
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (!function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     * @param string  $url    资源相对地址
     * @param boolean $domain 是否显示域名 或者直接传入域名
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $cdnurl = \think\Config::get('upload.cdnurl');
        if (is_bool($domain) || stripos($cdnurl, '/') === 0) {
            $url = preg_match($regex, $url) || ($cdnurl && stripos($url, $cdnurl) === 0) ? $url : $cdnurl . $url;
        }
        if ($domain && !preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain . $url;
        }
        return $url;
    }
}


if (!function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写
     * @param string $file 文件或目录
     * @return    bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/') . '/' . md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);
            return true;
        } elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
}

if (!function_exists('rmdirs')) {

    /**
     * 删除文件夹
     * @param string $dirname  目录
     * @param bool   $withself 是否删除自身
     * @return boolean
     */
    function rmdirs($dirname, $withself = true)
    {
        if (!is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }
        return true;
    }
}

if (!function_exists('copydirs')) {

    /**
     * 复制文件夹
     * @param string $source 源文件夹
     * @param string $dest   目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest . DS . $iterator->getSubPathName();
                if (!is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest . DS . $iterator->getSubPathName());
            }
        }
    }
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)) . mb_strtolower(mb_substr($string, 1));
    }
}

if (!function_exists('addtion')) {

    /**
     * 附加关联字段数据
     * @param array $items  数据列表
     * @param mixed $fields 渲染的来源字段
     * @return array
     */
    function addtion($items, $fields)
    {
        if (!$items || !$fields) {
            return $items;
        }
        $fieldsArr = [];
        if (!is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = $v['field'] ?? $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = $v['display'] ?? str_replace(['_ids', '_id'], ['_names', '_name'], $v['field']);
            $v['primary'] = $v['primary'] ?? '';
            $v['column'] = $v['column'] ?? 'name';
            $v['model'] = $v['model'] ?? '';
            $v['table'] = $v['table'] ?? '';
            $v['name'] = $v['name'] ?? str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model'];
            } else {
                // 优先判断使用table的配置
                $model = $v['table'] ? \think\Db::table($v['table']) : \think\Db::name($v['name']);
            }
            $primary = $v['primary'] ?: $model->getPk();
            $result[$v['field']] = isset($ids[$v['field']]) ? $model->where($primary, 'in', $ids[$v['field']])->column($v['column'], $primary) : [];
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $linedata = array_intersect_key($result[$n], $curr);
                    $v[$fieldsArr[$n]['display']] = $fieldsArr[$n]['column'] == '*' ? $linedata : implode(',', $linedata);
                }
            }
        }
        return $items;
    }
}

if (!function_exists('var_export_short')) {

    /**
     * 使用短标签打印或返回数组结构
     * @param mixed   $data
     * @param boolean $return 是否返回数据
     * @return string
     */
    function var_export_short($data, $return = true)
    {
        return var_export($data, $return);
    }
}

if (!function_exists('letter_avatar')) {
    /**
     * 首字母头像
     * @param $text
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = "#ffffff";
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="' . $bg . '" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="' . $color . '" text-anchor="middle" text-rights="admin" dominant-baseline="central">' . $first . '</text></svg>');
        $value = 'data:image/svg+xml;base64,' . $src;
        return $value;
    }
}

if (!function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255)
        ];
    }
}

if (!function_exists('check_nav_active')) {
    /**
     * 检测会员中心导航是否高亮
     */
    function check_nav_active($url, $classname = 'active')
    {
        $auth = \app\common\library\Auth::instance();
        $requestUrl = $auth->getRequestUri();
        $url = ltrim($url, '/');
        return $requestUrl === str_replace(".", "/", $url) ? $classname : '';
    }
}

if (!function_exists('check_cors_request')) {
    /**
     * 跨域检测
     */
    function check_cors_request()
    {
        if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] && config('fastadmin.cors_request_domain')) {
            $info = parse_url($_SERVER['HTTP_ORIGIN']);
            $domainArr = explode(',', config('fastadmin.cors_request_domain'));
            $domainArr[] = request()->host(true);
            if (in_array("*", $domainArr) || in_array($_SERVER['HTTP_ORIGIN'], $domainArr) || (isset($info['host']) && in_array($info['host'], $domainArr))) {
                header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
            } else {
                $response = Response::create('跨域检测无效', 'html', 403);
                throw new HttpResponseException($response);
            }

            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');

            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
                }
                if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                    header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
                }
                $response = Response::create('', 'html');
                throw new HttpResponseException($response);
            }
        }
    }
}

if (!function_exists('xss_clean')) {
    /**
     * 清理XSS
     */
    function xss_clean($content, $is_image = false)
    {
        return \app\common\library\Security::instance()->xss_clean($content, $is_image);
    }
}

if (!function_exists('url_clean')) {
    /**
     * 清理URL
     */
    function url_clean($url)
    {
        if (!check_url_allowed($url)) {
            return '';
        }
        return xss_clean($url);
    }
}

if (!function_exists('check_ip_allowed')) {
    /**
     * 检测IP是否允许
     * @param string $ip IP地址
     */
    function check_ip_allowed($ip = null)
    {
        $ip = is_null($ip) ? request()->ip() : $ip;
        $forbiddenipArr = config('site.forbiddenip');
        $forbiddenipArr = !$forbiddenipArr ? [] : $forbiddenipArr;
        $forbiddenipArr = is_array($forbiddenipArr) ? $forbiddenipArr : array_filter(explode("\n", str_replace("\r\n", "\n", $forbiddenipArr)));
        if ($forbiddenipArr && \Symfony\Component\HttpFoundation\IpUtils::checkIp($ip, $forbiddenipArr)) {
            $response = Response::create('请求无权访问', 'html', 403);
            throw new HttpResponseException($response);
        }
    }
}

if (!function_exists('check_url_allowed')) {
    /**
     * 检测URL是否允许
     * @param string $url URL
     * @return bool
     */
    function check_url_allowed($url = '')
    {
        //允许的主机列表
        $allowedHostArr = [
            strtolower(request()->host())
        ];

        if (empty($url)) {
            return true;
        }

        //如果是站内相对链接则允许
        if (preg_match("/^[\/a-z][a-z0-9][a-z0-9\.\/]+((\?|#).*)?\$/i", $url) && substr($url, 0, 2) !== '//') {
            return true;
        }

        //如果是站外链接则需要判断HOST是否允许
        if (preg_match("/((http[s]?:\/\/)+((?>[a-z\-0-9]{2,}\.)+[a-z]{2,8}|((?>([0-9]{1,3}\.)){3}[0-9]{1,3}))(:[0-9]{1,5})?)(?:\s|\/)/i", $url)) {
            $chkHost = parse_url(strtolower($url), PHP_URL_HOST);
            if ($chkHost && in_array($chkHost, $allowedHostArr)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('build_suffix_image')) {
    /**
     * 生成文件后缀图片
     * @param string $suffix 后缀
     * @param null   $background
     * @return string
     */
    function build_suffix_image($suffix, $background = null)
    {
        $suffix = mb_substr(strtoupper($suffix), 0, 4);
        $total = unpack('L', hash('adler32', $suffix, true))[1];
        $hue = $total % 360;
        list($r, $g, $b) = hsv2rgb($hue / 360, 0.3, 0.9);

        $background = $background ? $background : "rgb({$r},{$g},{$b})";

        $icon = <<<EOT
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
            <path style="fill:#E2E5E7;" d="M128,0c-17.6,0-32,14.4-32,32v448c0,17.6,14.4,32,32,32h320c17.6,0,32-14.4,32-32V128L352,0H128z"/>
            <path style="fill:#B0B7BD;" d="M384,128h96L352,0v96C352,113.6,366.4,128,384,128z"/>
            <polygon style="fill:#CAD1D8;" points="480,224 384,128 480,128 "/>
            <path style="fill:{$background};" d="M416,416c0,8.8-7.2,16-16,16H48c-8.8,0-16-7.2-16-16V256c0-8.8,7.2-16,16-16h352c8.8,0,16,7.2,16,16 V416z"/>
            <path style="fill:#CAD1D8;" d="M400,432H96v16h304c8.8,0,16-7.2,16-16v-16C416,424.8,408.8,432,400,432z"/>
            <g><text><tspan x="220" y="380" font-size="124" font-family="Verdana, Helvetica, Arial, sans-serif" fill="white" text-anchor="middle">{$suffix}</tspan></text></g>
        </svg>
EOT;
        return $icon;
    }
}
//CMS获取栏目地址
if (!function_exists('getCateUrl')) {
    //URL设置
    function getCateUrl($v)
    {
        //判断是否直接跳转
        if ($v['type'] == 'link') {
            $v['url'] = trim($v['outlink']);
        } else {
            if (config('site.route_switch')) {
                $v['url'] = '/' . $v['diyname'];
            } else {
                $v['url'] = '/cms/index/cate?id=' . $v['id'];
            }
            //判断是否跳转到下级栏目
            // if($v['is_next']==1){
            //     $is_next = Db::name('cate')->where('parentid',$v['id'])->order('sort ASC,id DESC')->find();
            //     if($is_next){
            //         $v['url'] = getCateUrl($is_next);
            //     }
            // }else{
            //     $moduleurl = Db::name('module')->where('id',$v['moduleid'])->value('name');
            //     if($v['catdir']){
            //         $v['url'] = url(request()->module().'/'.$v['catdir'].'/index', 'catId='.$v['id']);
            //     }else{
            //         $v['url'] = url(request()->module().'/'.$moduleurl.'/index', 'catId='.$v['id']);
            //     }
            // }

        }
        return $v['url'];
    }
}
//CMS获取显示地址
if (!function_exists('getShowUrl')) {
    //获取详情URL
    function getShowUrl($v, $cate_id)
    {
        if ($v) {
            $url = '/cms/index/show/cate_id/' . $cate_id . '/id/' . $v['id'];
        }
        return $url;
    }
}

if (!function_exists('changeFields')) {
    function changeFields($list, $cate_id)
    {
        $info = [];
        foreach ($list as $k => $v) {
            $url = getShowUrl($v, $cate_id);

            // $list[$k] = changeField($v);
            $info[$k] = $list[$k]; //定义中间变量防止报错
            $info[$k]['url'] = $url;
        }
        return $info;
    }
}


//添加违禁词出现次数
if (!function_exists('add_bansnum')) {
    function add_bansnum($ban)
    {
        try {
            $ban = trim($ban);
            $res = \think\Db::name('bansnum')->where('name', $ban)->find();
            if ($res) {
                //更新时间updatetime和增加num+1
                \think\Db::name('bansnum')->where('id', $res['id'])->update(['updatetime' => time(), 'num' => $res['num'] + 1]);
            } else {
                \think\Db::name('bansnum')->insert(['name' => $ban, 'num' => 1, 'createtime' => time()]);
            }
        } catch (\Throwable $th) {
            \think\Log::error('add_bansnum:' . $th->getMessage() . '行:' . $th->getLine());
        }
    }
}


/**
 * 验证违禁词
 * @param string $data 待验证数据
 * @param bool $relax 是否宽松验证 默认true
 */
if (!function_exists('bans')) {
    function bans($data, $relax = true)
    {
        if (is_array($data)) {
            $str = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $str = $data;
        }
        $bans = \think\Db::name('bans')->cache(60)->select();
        if (!$bans)
            return false;

        // $pattern = '/' . implode('|', array_map('preg_quote', $bans)) . '/i';
        //以下违禁词可以采用更高效的匹配方式
        $ban_cachetime_multiple = intval(\think\Config::get('site.ban_cachetime_multiple'));

        foreach ($bans as $k => $b) {
            $v = $b['name'];
            if (strpos($str, $v) !== false) {
                add_bansnum($v);
                // 缓存违禁词 如果在缓存中，则不报错,否则就返回违禁词(在设置的时间中就跳过)
                if ($relax && Cache::get("bans_" . $v)) {
                    // return false;
                    continue;
                } else {
                    $time = intval($b['level']) * $ban_cachetime_multiple ?? 1;
                    if ($time < 1) {
                        $time = 1;
                    }

                    Cache::set("bans_" . $v, $v, $time);
                    return $v;
                }
            }
        }
    }
}


/**
 * 获取网站列表
 */
if (!function_exists('getWebList')) {
    function getWebList()
    {
        $list = \think\Db::name('web')->whereNull('deletetime')->field('deletetime', true)->select();
        return $list;
    }
}

/**
 * 获取网站数组(lang)
 */
if (!function_exists('getWebListLangArray')) {
    function getWebListLangArray()
    {
        $list = \think\Db::name('web')->cache(120)->whereNull('deletetime')->field('lang')->column('lang');

        return $list;
    }
}


/**
 * 获取非主语言的网站数组(lang)
 */
if (!function_exists('getWebListOtherLangArray')) {
    function getWebListOtherLangArray()
    {
        $WebArray = getWebListLangArray();
        //去除默认语言 从数组中移除某个值
        $kk = array_search(config('default_lang'), $WebArray);
        if ($kk !== false) {
            unset($WebArray[$kk]);
        }
        return $WebArray;
    }
}


/**
 * 获取其他语言cate_id
 */
if (!function_exists('getOtherLangCateId')) {
    function getOtherLangCateId($cate_id, $lang)
    {
        $cate = Db::name('cate')->where('id', $cate_id)->where('lang', config('default_lang'))->find();
        if ($cate) {
            return Db::name('cate')->where('copy_id', $cate_id)->where('lang', $lang)->value('id');
        } else {
            throw new \Exception('未找到主语言该分类');
        }
    }
}


/**
 * 获取当前语言
 */
if (!function_exists('getDomainLang')) {
    function getDomainLang()
    {
        //获取当前域名的前缀
        $host = request()->host();

        // 获取域名前缀（二级域名）
        $domainPrefix = substr($host, 0, strpos($host, '.'));

        if (in_array($domainPrefix, getWebListLangArray())) {
            $lang = $domainPrefix;
        } else {
            $lang = config('default_lang');
        }
        return $lang;
    }
}
/**
 * 获取需要翻译的字段
 */
if (!function_exists('getFanyiTablesFieldsArray')) {
    function getFanyiTablesFieldsArray($table = null)
    {
        $fanyi_fields = \think\Db::name('fanyi_tables')->cache(120)->where('table_name', $table)->value('fanyi_fields');
        if (!$fanyi_fields) {
            return [];
        }
        $fanyi_fields = explode(',', $fanyi_fields);
        return $fanyi_fields;
    }
}


/**
 * 翻译
 * @param string $query 查询内容
 * @param string $from 自动识别
 * @param string $to 翻译语言
 * @return mixed|string
 */
if (!function_exists('fanyi')) {
    function fanyi($query, $from = 'auto', $to = 'zh')
    {

        return \fast\Fanyi::fanyi($query, $from, $to);
    }
}


/**
 * 翻译对应的网站语言
 * @param string $query 查询内容
 * @param string $from 自动识别
 * @param string $to 翻译语言
 * @return mixed|string
 */
if (!function_exists('getFanyiLang')) {
    function getFanyiLang($lang = '')
    {
        try {
            return Db::name('fanyi')->where('lang', $lang)->cache(120)->value(Config::get('site.fanyi_app'));
        } catch (\Throwable $th) {
            \think\Log::error('getFanyiLang:' . $th->getMessage() . '行:' . $th->getLine());
        }
    }
}


if (!function_exists('dbconfig')) {
    function dbconfig($key = null, $lang = null)
    {
        if(!$lang){
            $lang = getDomainLang();
        }

        $dbconfig = Cache::get('_DBCONFIG'.$lang);
        if (!$dbconfig) {
            $dbconfig = Db::name('dbconfig')
                ->cache(120)
                ->where('lang',$lang)
                ->column('value','name');

            Cache::set('_DBCONFIG'.$lang, $dbconfig);
        }
        if ($key) {
            return $dbconfig[$key] ?? null;
        }
        return $dbconfig;
    }
}
