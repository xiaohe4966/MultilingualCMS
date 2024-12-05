<?php
/*
 * @Author: he4966
 */

namespace app\common\library;

use think\Config;
use think\Log;
use think\Db;
use fast\Tree;


/**
 * LangCom操作类
 */
class LangCom
{
    use \traits\controller\Jump;
    public $cateList;//主栏目列表
    public $mainList;//主语言数据列表
    public static $insertNum = 0;
    /**
     * 更新栏目
     */
    public function updateCate($id = null)
    {
        $this->cateList = Db::table('fa_cate')->where('lang', '=', config('default_lang'))->select();
        $first  = \fast\Tree::instance()->init($this->cateList, 'parent_id')->getChild(0);
        foreach (getWebListOtherLangArray() as $key => $lang) {
            foreach ($first as $k => $v) {
                $this->自动保存栏目($v, $lang);
                // $second  = \fast\Tree::instance()->init($cateList, 'parent_id')->getChild($v['id']);
                $this->获取子目录($v, $lang);
                // $first[$k]['second'] = $second;
            }
        }
        // halt($first);
    }


    public function fanyiCate($id = null)
    {
        $num = 0;
        $cate = Db::table('fa_cate')->where('id', $id)->find();

        if ($cate) {
            $default_lang = config('default_lang');
            if ($cate['lang'] != $default_lang) {
                $this->error('非默认语言，不支持自动翻译');
            }


            $list = Db::table('fa_cate')->where('copy_id', $id)->select(); //多语言列表栏目

            if ($this->getCateSha1($cate) != $cate['fanyi_sha1']) {

                foreach ($list as $row) {
                    $res = $this->翻译保存栏目($row, $cate);
                    if ($res) {
                        $num++;
                    }
                }
                return $num;
            } else {
                $this->error('栏目内容未修改，无需翻译');
                // throw new \Exception("栏目内容未修改，无需翻译", -1);
            }
        }
    }

    /**
     * 删除栏目
     */
    public static function delCate($id, $delete = false)
    {
        $list = Db::name('cate')->where('copy_id', $id)->select();

        foreach ($list as $row) {

            if (isset($row['isnav_switch'])) {
                $row['isnav_switch'] = 0;
            }
            if (isset($row['status'])) {
                //状态:1=显示,2=隐藏
                $row['status'] = 2;
            }
            // 删除该栏目下面的所有文章
            if ($row['type'] == 'list')
                $res = Db::table($row['table_name'])->where('cate_id', $row['id'])->useSoftDelete('deletetime', time())->whereNull('deletetime')->delete();
        }
    }

    /**
     * 删除其他语言栏目
     */
    public function delOtherCate(){
        return $this->delOtherData('fa_cate');
    }



    /**
     * 删除其他语言数据
     */
    public function delOtherData($table_name){
        Log::info('删除其他语言数据'.$table_name.request()->ip());
        return Db::table($table_name)
            ->where('copy_id','>', 0)
            ->update(['deletetime'=> time()]);
            // ->delete();
    }






    /**
     * 更新其他数据
     */
    public function updateOtherData($table_name)
    {
        //使用该方法 表里面必须含有lang字段,    或包含cate_id字段
        $this->mainList = Db::table($table_name)->where('lang', '=', config('default_lang'))->whereNull('deletetime')->select();
        foreach (getWebListOtherLangArray() as $key => $lang) {
            foreach ($this->mainList as $k => $v) {
                unset($copy);
                unset($where);
                $copy = $v;
                unset($copy['id']);
                $copy['lang'] = $where['lang'] = $lang;
                $copy['copy_id'] = $where['id'] = $v['id'];
                $res = Db::table($table_name)->where($where)->find();
                $翻译 = true;
                $修改 = false;
                if ($res) {
                    if(isset($res['fanyi_switch']) && $res['fanyi_switch'] == 0){
                        //不翻译
                        $翻译 = false;
                    }
                    if ($this->getFanyiSha1($table_name,$copy) != $res['fanyi_sha1']) {
                        $修改 = true;
                    }
                    $copy['fanyi_num'] = $copy['fanyi_num'] + 1;
                }else{
                    $copy['fanyi_num'] = 1;
                }
                if ($翻译) {
                    $copy = $this->FanyiTableContent($copy, $lang, $table_name); //自动翻译栏目内容
                }else{

                }
                //如果设置了栏目id 则自动更新栏目id
                if (isset($copy['cate_id']) && $copy['cate_id']>0){
                    $copy['cate_id'] = getOtherLangCateId($v['cate_id'], $lang);//获取其他语言栏目id,⚠️只能传入主语言栏目id⚠️
                }
                $copy['fanyi_time'] = time();
                $copy['fanyi_sha1'] = $this->getFanyiSha1($table_name,$copy);

                if ($修改) {
                    Db::table($table_name)->where('id', $v['id'])->update($copy);
                    self::$insertNum++;
                }
                else {
                    Db::table($table_name)->insert($copy);
                    self::$insertNum++;
                }
                
            }
        }
        return self::$insertNum;
    }

    protected function 自动保存栏目($cate, $lang)
    {
        $cateNew = $cate;
        unset($cateNew['id']);
        $cateNew['lang'] = $lang;
        $cateNew['copy_id'] = $cate['id'];
        $res = Db::name('cate')->where('lang', $lang)->where('copy_id', $cate['id'])->find();
        if (!$res) {
            $cateNew['parent_id'] = $this->getLangParentId($cate['parent_id'], $lang);
            $cateNew = $this->FanyiTableContent($cateNew, $lang, 'fa_cate'); //自动翻译栏目内容

            $cateNew['fanyi_time'] = time();
            $cateNew['fanyi_num'] = 1;
            $cateNew['fanyi_sha1'] = $this->getCateSha1($cateNew);
            $insert_res = Db::name('cate')->insert($cateNew);
            if ($insert_res) {
                self::$insertNum++;
            }
        }
    }




    /**
     * 手动保存栏目
     * @param array $cate 新栏目数据
     * @param string $cate 原来栏目
     * @param int $fanyi_switch 1=自动翻译,0=手动翻译
     * @return mixed
     */
    protected function 翻译保存栏目($cateNew, $Oldcate)
    {
        $fanyi_switch = $cateNew['fanyi_switch'];
        if ($fanyi_switch == 1) {

            $new_cate_id = $cateNew['id'];
            // unset($cateNew['id']);
            // $cateNew['copy_id'] = $cate['id'];
            // $res = Db::name('cate')->where('lang', $lang)->where('copy_id', $cate['id'])->find();
            // if (!$res) {
            //     $cateNew['parent_id'] = $this->getLangParentId($cate['parent_id'], $lang);

            //     $cateNew = $this->FanyiTableContent($cateNew, $lang, 'fa_cate'); //自动翻译栏目内容
            //     $cateNew['fanyi_time'] = time();
            //     $cateNew['fanyi_num'] = 1;
            //     $cateNew['fanyi_sha1'] = $this->getCateSha1($cateNew);
            //     Db::name('cate')->insert($cateNew);
            // } else {
            // halt($cate);

            $update_cate = $this->主栏目到其他语言($Oldcate, $cateNew['lang'], 'fa_cate'); //自动翻译栏目内容
            $update_cateNew['fanyi_time'] = time();
            $update_cateNew['fanyi_num'] = $cateNew['fanyi_num'] + 1;
            return Db::name('cate')->where('id', $new_cate_id)->update($update_cate);
   

            // }
        }
    }

    /**
     * 自动翻译栏目内容
     * @param array $cateNew 栏目数据
     * @param string $lang 翻译语言
     * @param string $table_name
     * @return mixed
     */
    public function FanyiTableContent($cateNew, $lang, $table_name = 'fa_cate')
    {
        $fieldsArr = getFanyiTablesFieldsArray($table_name); //获取需要翻译的字段[]
        foreach ($fieldsArr as $field) {
            // halt($cateNew);
            // halt(getFanyiLang($cateNew['lang']));
            //循环翻译需要的字段  如果长度大于0 就翻译
            $cateNew[$field] = strlen($cateNew[$field]) > 0 ? fanyi($cateNew[$field], 'auto', getFanyiLang($cateNew['lang'])) : null;
        }
        return $cateNew;
    }

    /**
     * 点击翻译栏目内容
     * @param array $cateNew 栏目数据
     * @param string $lang 翻译语言
     * @param string $table_name
     * @return mixed
     */
    public function 主栏目到其他语言($cate, $lang, $table_name = 'fa_cate')
    {
        $fieldsArr = getFanyiTablesFieldsArray($table_name); //获取需要翻译的字段[]
        $cateNew = [];
        foreach ($fieldsArr as $field) {
            // halt($cate);
            // halt(getFanyiLang($cate['lang']));
            //循环翻译需要的字段  如果长度大于0 就翻译
            $cateNew[$field] = strlen($cate[$field]) > 0 ? fanyi($cate[$field], 'auto', getFanyiLang($lang)) : null;
        }
        return $cateNew;
    }

    protected function getLangParentId($id, $lang)
    {

        if ($id > 0) {
            $res = Db::name('cate')->where('lang', $lang)->where('copy_id', $id)->find();
            if ($res) {
                return $res['id'];
            }
        }

        return 0;
    }


    protected function 获取子目录($v, $lang)
    {
        $second  = \fast\Tree::instance()->init($this->cateList, 'parent_id')->getChild($v['id']);
        if ($second) {

            $this->自动保存栏目($v, $lang);

            foreach ($second as $k => $vv) {
                $this->获取子目录($vv, $lang);
            }
        } else {
            $this->自动保存栏目($v, $lang);
        }
    }


    public function getCateSha1($cate)
    {
        // $fieldsArr = getFanyiTablesFieldsArray('fa_cate'); //获取需要翻译的字段[]
        // $str = '';
        // foreach ($fieldsArr as $field) {
        //     $str .= $cate[$field];
        // }
        // return sha1($str);
        $this->getFanyiSha1('fa_cate', $cate);
    }

    public function getFanyiSha1($table_name,$arr)
    {
        $fieldsArr = getFanyiTablesFieldsArray($table_name); //获取需要翻译的字段[]
        $str = '';
        foreach ($fieldsArr as $field) {
            $str .= $arr[$field];
        }
        return sha1($str);
    }


    public function update_cate_sha1()
    {
        $list = Db::name('cate')->select();
        foreach ($list as $cate) {

            $sha1 =  $this->getCateSha1($cate);

            Db::name('cate')->where('id', $cate['id'])->update(['fanyi_sha1' => $sha1]);
        }
    }
}
