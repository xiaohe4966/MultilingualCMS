<?php
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
//   |_|| .__/|_|  |_|\___|  \____|_|  |_|____/     | DATETIME: 2024/05/18/
//      |_|                                         | TpMeCMS

namespace app\cms\controller;

use think\Db;
use think\Config;
use think\Log;
use app\common\library\Email;

class Gbook extends Cms
{

    protected $noNeedLogin = ['*']; //自己需要写的方法 这个是不需要登陆的其他则为需要登陆
    protected $noNeedRight = '*';
    protected $layout = '';


    public function _initialize()
    {
        parent::_initialize();
    }


    /**
     * 添加留言
     *
     * @return void
     */
    public function add($captcha = null)
    {
        if (Config::get('site.add_gbook_switch')) {
            //是否需要验证码
            if (Config::get('site.gbook_captcha_switch')) {
                $captchaResult = \think\Validate::is($captcha, 'captcha');
                if (!$captchaResult) {
                    $this->error(__('Captcha is incorrect'));
                }
            }


            $data = $this->request->param();
            $ban_res = bans($data, true); //违禁词检测 禁止宽松验证
            if ($ban_res) {
                $this->error('您提交的数据含有敏感词' . (is_array($ban_res) ? implode(',', $ban_res) : $ban_res));
            }
            //没有的参数也可以插入
            $data['ip'] = $this->request->ip();
            $data['user_agent'] = $this->request->server('HTTP_USER_AGENT');
            // 过滤post数组中的非数据表字段数据
            $data['content'] = htmlspecialchars($data['content']);
 
            $user = new \app\admin\model\Gbook($data);
            // 过滤post数组中的非数据表字段数据
            $res = $user->allowField(true)->save();
            if ($res) {
                // 发送邮件
                $this->send_email($data);
                $this->success('留言成功');
            } else {
                $this->error('留言失败');
            }
        } else {
            $this->error('暂未开通留言');
        }
    }


    protected function send_email($data)
    {
        try {
            if (Config::get('site.gbook_to_email_switch')) {

                $toemail = Config::get('site.gbook_email');

                if (!Validate::is($toemail, "email")) {
                    Log::error('留言发送邮箱失败' . $toemail);
                }
                // \think\Config::set('site', array_merge(\think\Config::get('site'), $row));
                $email = new Email;
                $result = $email
                    ->to($toemail)
                    ->subject("来自" . Config::get('site.name') . "的留言")
                    ->message('<div style="min-height:550px; padding: 100px 55px 200px;">' . json_encode($data, JSON_UNESCAPED_UNICODE) . '</div>')
                    ->send();
                if ($result) {
                    Log::success('留言发送邮件成功' . $toemail);
                } else {
                    Log::error('留言发送邮箱失败' . $toemail . ' ' . $email->getError());
                }
            }
        } catch (\Throwable $th) {
            Log::error('留言发送邮箱ERROR' . $toemail . ' ' . $th->getMessage());
        }
    }
}
