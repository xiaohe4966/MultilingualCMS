<?php
/*
 * @Author: XiaoHe
 */
/*
 * @Author: XiaoHe
 */
/*
 * @Author: XiaoHe
 */
                                                                                                                                                                                                                                                                                                                                        
// TTTTTTTTTTTTTTTTTTTTTTT                  MMMMMMMM               MMMMMMMM                                CCCCCCCCCCCCMMMMMMMM               MMMMMMMM  SSSSSSSSSSSSSSS 
// T:::::::::::::::::::::T                  M:::::::M             M:::::::M                             CCC::::::::::::M:::::::M             M:::::::MSS:::::::::::::::S
// T:::::::::::::::::::::T                  M::::::::M           M::::::::M                           CC:::::::::::::::M::::::::M           M::::::::S:::::SSSSSS::::::S
// T:::::TT:::::::TT:::::T                  M:::::::::M         M:::::::::M                          C:::::CCCCCCCC::::M:::::::::M         M:::::::::S:::::S     SSSSSSS
// TTTTTT  T:::::T  TTTTTppppp   ppppppppp  M::::::::::M       M::::::::::M   eeeeeeeeeeee          C:::::C       CCCCCM::::::::::M       M::::::::::S:::::S            
//         T:::::T       p::::ppp:::::::::p M:::::::::::M     M:::::::::::M ee::::::::::::ee       C:::::C             M:::::::::::M     M:::::::::::S:::::S            
//         T:::::T       p:::::::::::::::::pM:::::::M::::M   M::::M:::::::Me::::::eeeee:::::ee     C:::::C             M:::::::M::::M   M::::M:::::::MS::::SSSS         
//         T:::::T       pp::::::ppppp::::::M::::::M M::::M M::::M M::::::e::::::e     e:::::e     C:::::C             M::::::M M::::M M::::M M::::::M SS::::::SSSSS    
//         T:::::T        p:::::p     p:::::M::::::M  M::::M::::M  M::::::e:::::::eeeee::::::e     C:::::C             M::::::M  M::::M::::M  M::::::M   SSS::::::::SS  
//         T:::::T        p:::::p     p:::::M::::::M   M:::::::M   M::::::e:::::::::::::::::e      C:::::C             M::::::M   M:::::::M   M::::::M      SSSSSS::::S 
//         T:::::T        p:::::p     p:::::M::::::M    M:::::M    M::::::e::::::eeeeeeeeeee       C:::::C             M::::::M    M:::::M    M::::::M           S:::::S
//         T:::::T        p:::::p    p::::::M::::::M     MMMMM     M::::::e:::::::e                 C:::::C       CCCCCM::::::M     MMMMM     M::::::M           S:::::S
//       TT:::::::TT      p:::::ppppp:::::::M::::::M               M::::::e::::::::e                 C:::::CCCCCCCC::::M::::::M               M::::::SSSSSSS     S:::::S
//       T:::::::::T      p::::::::::::::::pM::::::M               M::::::Me::::::::eeeeeeee          CC:::::::::::::::M::::::M               M::::::S::::::SSSSSS:::::S
//       T:::::::::T      p::::::::::::::pp M::::::M               M::::::M ee:::::::::::::e            CCC::::::::::::M::::::M               M::::::S:::::::::::::::SS 
//       TTTTTTTTTTT      p::::::pppppppp   MMMMMMMM               MMMMMMMM   eeeeeeeeeeeeee               CCCCCCCCCCCCMMMMMMMM               MMMMMMMMSSSSSSSSSSSSSSS   
//                        p:::::p                                                                                                                                       
//                        p:::::p                                                                                                                                       
//                       p:::::::p                                                                                                                                      
//                       p:::::::p                                                                                                                                      
//                       p:::::::p                                                                                                                                      
//                       ppppppppp                                                                                                                                      
                                                                                                                                                                     
//  _____      __  __         ____ __  __ ____  
// |_   __ __ |  \/  | ___   / ___|  \/  / ___|     | AUTHOR: Xiaohe
//   | || '_ \| |\/| |/ _ \ | |   | |\/| \___ \     | EMAIL: 496631085@qq.com
//   | || |_) | |  | |  __/ | |___| |  | |___) |    | WECHAT: he4966
//   |_|| .__/|_|  |_|\___|  \____|_|  |_|____/     | DATETIME: 2024/04/17
//      |_|                                         | TpMeCMS
use think\Route;

use think\Db;
use think\Config;

$installLockFile = APP_PATH . 'admin' . DS . 'command' . DS . 'Install' . DS. "install.lock";

if(!is_file($installLockFile)){
    return [
        //别名配置,别名只能是映射到控制器且访问时必须加上请求的方法
        '__alias__'   => [
        ],
        //变量规则
        '__pattern__' => [
        ],
    
    //        域名绑定到模块
    //        '__domain__'  => [
    //            'admin' => 'admin',
    //            'api'   => 'api',
    //        ],
    ];
}




// Route::rule('new/:id','News/read','GET|POST'); //定义多种请求规则
// Route::get('new/<id>','News/read'); // 定义GET请求路由规则
// Route::post('new/<id>','News/update'); // 定义POST请求路由规则
// Route::put('new/:id','News/update'); // 定义PUT请求路由规则
// Route::delete('new/:id','News/delete'); // 定义DELETE请求路由规则
// Route::any('new/:id','News/read'); // 所有请求都支持的路由规则


//加入多语言路由
$weblist = getWebList();

foreach ($weblist as $key => $web) {
    // Route::domain($web['lang'],'?lang='.$web['lang']);
    // Route::domain($web['lang'], '?lang='.$web['lang']);

    Route::domain($web['lang'],'', ['lang' => $web['lang']]);
    
}

// Route::domain('*', 'cms?lang=*');

Route::any('/sitemap.xml','cms/index/sitemap');








//如果开启了自定义路由
if(Config::get('site.route_switch'))// 兼容自定义路由，如果需要后台路由开关控制，请取消注释此行   TpMeCms
    Route::any('/index','cms/index/index');//可以自行更改cms首页路由

    foreach(Db::name('cate')->select() as $key=>$val){

        switch ($val['type']) {
            case 'page':

                Route::any($val['diyname'],'cms/index/cate?id='.$val['id']);
                break;
            case 'list':
                Route::any($val['diyname'],'cms/index/cate?id='.$val['id']);
                // Route::any('news/:id','cms/index/cate?cate_id=59');

                Route::any($val['diyname'].'/page/:id','cms/index/cate/id/'.$val['id']);

                Route::any($val['diyname'].'_show/:id','cms/index/show?cate_id='.$val['id']);
                break;


            case 'link':
                Route::any($val['diyname'],'cms/index/cate?id='.$val['id']);
                break;
                
            default:
                # code...
                break;
        }
    }


