<?php
/*
 * @Author: he4966
 */

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
/**
 * 示例接口
 */
class Demo extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['test', 'test1','fanyi','cate','update_cate_sha1'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];

    /**
     * 测试方法
     *
     * @ApiTitle    (测试名称)
     * @ApiSummary  (测试描述信息)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/demo/test/id/{id}/name/{name})
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name="id", type="integer", required=true, description="会员ID")
     * @ApiParams   (name="name", type="string", required=true, description="用户名")
     * @ApiParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
         'code':'1',
         'msg':'返回成功'
        })
     */
    public function test()
    {
        $this->success('返回成功', $this->request->param());
    }

    /**
     * 无需登录的接口
     *
     */
    public function test1()
    {
        $this->success('返回成功', ['action' => 'test1']);
    }

    /**
     * 需要登录的接口
     *
     */
    public function test2()
    {
        $this->success('返回成功', ['action' => 'test2']);
    }

    /**
     * 需要登录且需要验证有相应组的权限
     *
     */
    public function test3()
    {
        $this->success('返回成功', ['action' => 'test3']);
    }

    public function fanyi($str=''){
        $textToTranslate = '你好';
        if($str)
            $textToTranslate = $str;

        $fromLang = 'zh';
        $toLang = 'en';

        // $baiduTranslate = new \fast\BaiduTranslate();
        // try {
        //     $translatedText = $baiduTranslate->translate($textToTranslate, $fromLang, $toLang);
        //     echo "翻译结果：" . $translatedText;
        // } catch (\Exception $e) {
        //     echo "发生错误：" . $e->getMessage();
        // }


        $fromLang = 'zh';
        $toLang = 'ru';
        echo \fast\Fanyi::fanyi($textToTranslate, $fromLang, $toLang);

    }


    public function cate(){
        (new \app\common\library\LangCom())->updateCate();
    }



    protected function getCateSha1($cate)
    {
        $fieldsArr = getFanyiTablesFieldsArray('fa_cate');
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
