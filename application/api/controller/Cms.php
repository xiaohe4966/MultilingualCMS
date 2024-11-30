<?php
/*
 * @Author: XiaoHe
 */

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;
use think\Cache;
use think\Config;
/**
 * CMS接口
 */
class Cms extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['link_click', 'add_gbook', 'link_check', 'update_sitemap'];
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
     * 友情链接点击接口
     * @param int $id 友情链接ID
     */
    public function link_click($id = null)
    {
        if($id){
            $ip = $this->request->ip();
            $cache_key = 'link_click_'.$id.'_'.$ip;
            $cache_time = 10;//多少秒以内点击同一id算一次
            if(!Cache::get($cache_key)){
                $res = Db::name('link')->where('id',$id)->setInc('views',1);
                if($res){
                    $this->success('点击成功');
                }else{
                    $this->error('增加失败');
                }
                Cache::set($cache_key,1,$cache_time);
            }else{
              $this->error('无效点击');
            }

          
        }else{
            $this->error('参数错误');
        }

    }

    /**
     * 友情链接检测接口
     */
    public function link_check(){
        $list = Db::name('link')->where('switch',1)->select();
        $error = [];
        $link_auto_hide = Config::get('site.link_auto_hide');
        if($list)
        foreach ($list as $key => $link) {
            $正常 = true;
            //判断前缀是否有http
            if(strpos($link['url'],'http') === false){
                $url = 'http://'.$link['url'];
            }else{
                $url = $link['url'];
            }

            $ql = \QL\QueryList::get($url, [], [
                'timeout' => 30, // 设置请求超时时间
                'redirect' => true // 开启重定向
            ]);
            try {
                $html = $ql->getHTML();
                if (empty($html)) {
                    $error[] = $link['url'];
                    $正常 = false;
                }
            } catch (\Exception $e) {
                $error[] = $link['url'];
                $正常 = false;
            }
            if(!$正常 && $link_auto_hide){
               Db::name('link')->where('id',$link['id'])->update(['switch'=>0, 'updatetime'=>time(),'memo'=>$link['memo'].'访问错误,自动隐藏']);
            }
        }
        if($error){
            $this->error('以下链接无法访问:'.implode(',',$error));
        }
        $this->success('所有链接正常');
    }

    /**
     * 更新Sitemap
     */
    public function update_sitemap(){
        $this->updateSitemapXml();
        $this->success('更新成功,请手动提交到搜索引擎');
    }

    protected function updateSitemapXml(){
        $html = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $domain = $this->request->domain();
        $urlArr = [];
        if(Config::get('site.route_switch')){
            foreach(Db::name('cate')->whereNull('deletetime')->select() as $key=>$val){

                switch ($val['type']) {
                    case 'page':
                        $url = $domain.'/'.$val['diyname'].'.html';
                       
                        break;
                    case 'list':
                        $url = $domain.'/'.$val['diyname'].'.html';
                        
                        $list = Db::table($val['table_name'])->whereNull('deletetime')->field('id')->select();
                        foreach ($list as $key => $value) {
                            $show_url = $domain.'/'.$val['diyname'].'_show/'.$value['id'].'.html';

                            if(!in_array($show_url,$urlArr)){
                                $urlArr[] = $show_url;
                                $html .= '<url>
<loc>'.$show_url.'</loc>
</url>';
                            }
                        }
                        break;
        
        
                    case 'link':
                        $url = $domain.'/'.$val['diyname'].'.html';
                     
                        break;
                        
                    default:
                        # code...
                        break;
                }
                if(!in_array($url,$urlArr)){
                    $urlArr[] = $url;
                    $html .= '<url>
<loc>'.$url.'</loc>
</url>';
                }
            }   
        }else{
            foreach(Db::name('cate')->select() as $key=>$val){

                switch ($val['type']) {
                    case 'page':
                        $url = $domain.'/cms/index/cate?id='.$val['id'].'.html';
                      
                        break;
                    case 'list':
                        $url = $domain.'/cms/index/cate?id='.$val['id'].'.html';
        
                        $list = Db::table($val['table_name'])->whereNull('deletetime')->field('id')->select();
                        foreach ($list as $key => $value) {
                            $show_url = $domain.'/cms/index/show/cate_id/'.$val['id'].'?id='.$value['id'].'.html';

                            if(!in_array($show_url,$urlArr)){
                                $urlArr[] = $show_url;
                                $html .= '<url>
<loc>'.$show_url.'</loc>
</url>';
                            }
                        }
                        break;
        
        
                    case 'link':
                        $url = $domain.'/cms/index/cate?id='.$val['id'].'.html';
                       
                        break;
                        
                    default:
                        # code...
                        break;
                }

                if(!in_array($url,$urlArr)){
                    $urlArr[] = $url;
                    $html .= '<url>
<loc>'.$url.'</loc>
</url>';
               
                }
            }   
        }
   
        $html .= '</urlset>';
   
        file_put_contents(ROOT_PATH . 'public/sitemap', $html);
    }
}
