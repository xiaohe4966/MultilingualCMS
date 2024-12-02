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

    public $cateList;
    /**
     * 更新栏目
     */
    public function updateCate($id = null)
    {
        $this->cateList = Db::table('fa_cate')->where('lang', '=', config('default_lang'))->select();

        // $list  = \fast\Tree::instance()->init($cateList, 'parent_id')->getChildren(0);
        // halt($list);


        $first  = \fast\Tree::instance()->init($this->cateList, 'parent_id')->getChild(0);
        $fanyiWebArray = getWebListLangArray();

        //去除默认语言 从数组中移除某个值
        $kk = array_search(config('default_lang'), $fanyiWebArray);
        if ($kk !== false) {
            unset($fanyiWebArray[$kk]);
        }

        foreach ($fanyiWebArray as $key => $lang) {


            foreach ($first as $k => $v) {
                $this->自动保存栏目($v, $lang);
                // $second  = \fast\Tree::instance()->init($cateList, 'parent_id')->getChild($v['id']);
                $this->获取子目录($v, $lang);
                // $first[$k]['second'] = $second;
            }
        }
        // halt($first);

    }


    public function editCate($id = null)
    {
        $cate = Db::table('fa_cate')->where('id', $id)->find();

        if ($cate) {

            $default_lang = config('default_lang');
            if ($default_lang == $cate['lang']) {//如果修改的是默认语言就需要同步修改多语言栏目
                $list = Db::table('fa_cate')->where('copy_id', $id)->select(); //多语言列表栏目
                if ($this->getCateSha1($cate) != $cate['fanyi_sha1']) {
    
                    foreach ($list as $row) {
                        $this->手动保存栏目($row, $cate['lang'],1);
                    }
                }
            }else{
                // ???????
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

    protected function 自动保存栏目($cate, $lang)
    {
        $cateNew = $cate;
        unset($cateNew['id']);
        $cateNew['lang'] = $lang;
        $cateNew['copy_id'] = $cate['id'];
        $res = Db::name('cate')->where('lang', $lang)->where('copy_id', $cate['id'])->find();
        if (!$res) {
            $cateNew['parent_id'] = $this->getLangParentId($cate['parent_id'], $lang);
            $cateNew = $this->FanyiCateContent($cateNew, $lang, 'fa_cate'); //自动翻译栏目内容

            $cateNew['fanyi_time'] = time();
            $cateNew['fanyi_num'] = 1;
            $cateNew['fanyi_sha1'] = $this->getCateSha1($$cateNew);
            Db::name('cate')->insert($cateNew);
        }
    }




    protected function 手动保存栏目($cate, $lang, $fanyi_switch = 1)
    {
        $cateNew = $cate;
        unset($cateNew['id']);
        $cateNew['lang'] = $lang;
        $cateNew['copy_id'] = $cate['id'];
        $res = Db::name('cate')->where('lang', $lang)->where('copy_id', $cate['id'])->find();
        if (!$res) {
            $cateNew['parent_id'] = $this->getLangParentId($cate['parent_id'], $lang);

            $cateNew = $this->FanyiCateContent($cateNew, $lang, 'fa_cate'); //自动翻译栏目内容
            $cateNew['fanyi_time'] = time();
            $cateNew['fanyi_num'] = 1;
            $cateNew['fanyi_sha1'] = $this->getCateSha1($$cateNew);
            Db::name('cate')->insert($cateNew);
        } else {
            if ($fanyi_switch == 1) {
                $cateNew = $this->FanyiCateContent($cateNew, $lang, 'fa_cate'); //自动翻译栏目内容
                $cateNew['fanyi_time'] = time();
                $cateNew['fanyi_num'] += 1;
            } else {
                unset($cateNew['fanyi_time']);
                unset($cateNew['fanyi_num']);
            }
            Db::name('cate')->where('lang', $lang)->where('copy_id', $cate['id'])->update($cateNew);
        }
    }

    /**
     * 自动翻译栏目内容
     * @param array $cateNew 栏目数据
     * @param string $lang 翻译语言
     * @param string $table_name
     * @return mixed
     */
    public function FanyiCateContent($cateNew, $lang, $table_name = 'fa_cate')
    {
        $fieldsArr = getFanyiTablesFieldsArray('fa_cate'); //获取需要翻译的字段[]
        foreach ($fieldsArr as $field) {
            //循环翻译需要的字段  如果长度大于0 就翻译
            $cateNew[$field] = strlen($cateNew[$field]) > 0 ? fanyi($cateNew[$field], 'auto', getFanyiLang($lang)) : null;
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
        $fieldsArr = getFanyiTablesFieldsArray('fa_cate'); //获取需要翻译的字段[]
        $str = '';
        foreach ($fieldsArr as $field) {
            $str .= $cate[$field];
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
