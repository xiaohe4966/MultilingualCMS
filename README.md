该MultilingualCMS(多国语言CMS)是一款基于FastAdmin框架（version:1.5.0.20240328）开发的，FastAdmin基于ThinkPHP5+Bootstrap开发的框架。

## 界面截图

![超级管理员才有的设置](https://gitee.com/xiaohe4966/cms/raw/master/public/demo/demo_config.jpg "超级管理员才有的设置")

## 环境要求
    操作系统：Linux、Windows、Mac OS
    Web 服务器：Apache、Nginx
    PHP 版本：>= 7.2 且 <= 7.4 (推荐 PHP7.4 版本)
    MySQL 版本：>= 5.6 且 <= 8.0 (需支持 innodb 引擎)

## 下载方法
    下载默认直接:
    `git clone https://github.com/xiaohe4966/MultilingualCMS.git`

    下载某个分支:
    `git clone -b 分支名 https://github.com/xiaohe4966/MultilingualCMS.git`
## 安装方法
     
    修改网站运行目录为public
    修改伪静态！！！修改伪静态！！修改伪静态！
    正常Nginx伪静态
    ```
    location ~* (runtime|application)/{
        return 403;
    }
    location / {
        if (!-e $request_filename){
            rewrite  ^(.*)$  /index.php?s=$1  last;   break;
        }
    }
    ```

            
    
    文件在application/database.php里面修改   //或者在根目录.env里面更改(没有此文件请忽略)

    然后安装即可  
          
    cms首页： 打开域名会跳转到/cms/index/index，如果不需要可以自行注释跳转 application/index/controller/Index.php
     

  
## 使用流程
    安装好框架后
    只需要复制表修改表字段（只需要字段）
    一键生成后台菜单命令（增删改查）
    添加/修改栏目选择表即可（如果数据库表名未修改不用修改）

## 注意事项及说明
* 数据库设计要求
    * 字段命名要求https://doc.fastadmin.net/doc/database.html
    * 使用后台的《在线命令管理》或者用命令php think crud -t 表名生成CRUD时会自动生成对应的HTML元素和组件
* 数据库说明
    * xx代表表前缀
    * xx_cate CMS栏目的栏目，不能删除（可以添加其他通用字段
    * xx_page CMS的单页内容信息，不能删除（可以添加其他通用字段
    * xx_user 在原Fastadmin的user表
    * xx_admin 登陆后台的账号
    * xx_command Fastadmin的一键命令插件记录
    * xx_config 网站常规配置就在这里面，在后台可直接添加
    * xx_news 列表页的内容，通用都可以复制此表改名改备注即可（列表页面的 deletetime 字段不要删除，用户在后台删除后，可以进入回收站，但前台会看不见，以防误操作）





## 配置
    微信配置在  后台常规管理->系统配置->微信->设置小程序公众号等资料
![设置图](https://he4966.cn/uploads/tpmecms/1.png "设置图")

    调试模式开启application/config.php  
    // 应用调试模式
    'app_debug' => Env::get('app.debug', 1),    //或者在根目录.env里面更改(没有此文件请忽略)

    数据库配置修改application/database.php里面修改   //或者在根目录.env里面更改(没有此文件请忽略)

## 使用标签
    thinkphp5.0 手册(https://www.kancloud.cn/manual/thinkphp5/125005)

### 后台参数标签
```html
    {$site.后台添加的字段}
    <!-- 示例 -->
    {$site.name} //网站名称
    {$site.keywords} //网站关键字
    {$site.description} //网站描述
    {$site.beian} //网站备案号
    {$site.logo} //网站logo
    ...可以自己添加
```
### 获取config.php里面的配置
```html
    {$Think.config.fastadmin.version}
```
### 获取栏目名
```html
    {tp:cate id="38" type="name"}
```
### 获取栏目地址链接 
```html
    {tp:cate id="38" type="url"}
```
    //这个url在application/common.php  getCateUrl方法里面（可自行修改封装
### 获取栏目某个字段
```html
    {tp:cate id="38" type="字段"} 
```

### 获取栏目里面的 列表数据
```html
    <!-- 循环的导航栏 某个栏目id里面新闻或者自己添加的栏目里面的列表 -->
    {volist name="nav" id="v"}
    {if $v.id==67}
        {volist name="$v.childlist" id="v2"}
        
        <div class="product_con">
            <div class="pro_tit">
            <img src="__CMS__/images/he4966.png" alt="">{$v2.name}
            </div>
        <ul>
            <!-- 因为列表里面是含有子栏目的列表 所以要判断if 如果要包含子栏目的数据可以取消判断if-->
            {tp:list name="p" id="$v2['id']">}
            {if $v2.id==$p.cate_id}
            <li><a href="{$p.url}">{$p.title}</a></li>
            {/if}
            {/tp:list}
        </ul>

        </div>
        {/volist}
    {/if}
    {/volist}
```

### 🗂️获取列表
    参数:id(栏目id),name(变量名 默认$list),pagesize(一页数量),where(条件),limit(每页数量 默认0),order(排序 默认'weigh DESC,id DESC')
```html
    <!--id是栏目ID 取这栏目标里面数据库的数据   如果要具体某个栏目下面的数据(前提是这个栏目是栏目的N个子栏目) 可以加where="cate_id=68"-->
    {tp:list name="list" id="1" limit="3"}
        //{$list.这个栏目表里面的字段}
        {$list.title}
        {$list.image}
        {$list.url}//这个url在application/common.php 里面的getShowUrl方法里面（可自行修改封装
    {/tp:list}
```

    
```html
    🌹只能在列表页面使用 列表页字带list变量
    {volist name="list" id="v"}
    <a href="{$v.url}">{$v.title}</a>
    {/volist}
```
#### 🗂️获取列表加条件 where里面是条件 
```html
    <!-- 推荐条件写法 -->
    {tp:list id="61" limit="2" name="v" where="FIND_IN_SET('1', hotdata)"}
    <div class="news-tu">

        <a href="{$v.url}">
        <b class="dot">{$v.title}</b>
        <i>{:date('Y-m-d',$v.createtime)}</i>
        <p> {$v.description}</p>
        <img src="{$v.image?$v.image:'__CMS__/images/news.jpg'}">
        </a>
    </div>
    {/tp:list}
```
#### 🗂️获取列表加条件 FIND_IN_SET多个 
```html
    <!-- 多个推荐条件写法  推荐里面包含1或2的列表 推荐:1=推荐,2=特荐,3=头条,4=精华-->
    {tp:list id="61" limit="2" name="v" where="FIND_IN_SET('1', hotdata) OR FIND_IN_SET('2', hotdata)"}
    {/tp:list}
```

#### 🗂️获取新增列表数据示例
```html
    <!-- 比如我在fa_news表复制一份表名为fa_pro加入了其他字段 然后我要获取这个列表的数据 -->
    <!-- 和上面一样 栏目里面含有这个表名 只需要填写对应栏目id即可 -->
    {tp:list name="v" id="67" limit="10"  where="FIND_IN_SET('1', hotdata) AND cate_id=68"}
        <!-- 循环你的数据 -->
        <a href="{$v.url}">{$v.title}</a>
    {/tp:list}
```

### 循环多级栏目 及 当前栏目高亮
```html
    {volist name="nav" id="v"}                            
        {if $v.childlist}
        <li class="dropdown {if $cate['id'] eq $v['id']  OR $cate['is_top'] eq $v['id']}current{/if}   "><a href="{$v.url}">{$v.name}</a>                                
            <ul>
                {volist name="$v.childlist" id="v2"}                                       
                
                    {if $v2.childlist}                                            
                        <li class="dropdown"><a href="{$v2.url}">{$v2.name}</a>
                            <ul>
                                {volist name="$v2.childlist" id="v3"}
                                <li><a href="{$v3.url}">{$v3.name}</a></li>
                                {/volist}                                
                            </ul>
                        </li>
                    {else /}
                    
                        <li><a href="{$v2.url}">{$v2.name}</a></li>
                    {/if}
                {/volist}
            </ul>
        </li>   
        {else /}
            <li {if $cate['id'] eq $v['id']}class="current"{/if}><a href="{$v.url}">{$v.name}</a></li>
        {/if}
    {/volist}
``` 
    这段代码通过循环遍历和条件判断，动态生成了多级导航菜单。使用了volist标签来循环遍历导航数据。其中name="nav"表示遍历的变量名为nav(导航栏目)，id="v"表示将遍历的每个元素赋值给变量v。

                                                       
    在遍历过程中，使用if标签判断当前导航是否有子菜单(childlist)，如果有，则将其渲染为下拉菜单。下拉菜单中同样使用了volist标签来遍历子菜单数据。子菜单同样存在是否有子菜单的情况，通过嵌套使用volist标签来实现。

    如果导航没有子菜单，则直接渲染为一个普通的导航项。在导航项中，通过if标签判断当前导航项是否为当前选中状态，如果是，则添加class="current"。


### 列表页分页示例
#### 上一页
```html
    {if $page_data['prev_page']}<li><a href="{$page_data.prev_page.url}"><span class="fa fa-angle-left"></span></a></li>{/if}
```

#### 循环中间页码并高量当前页
```html
    {volist name="$page_data['list']" id="v"}
    <li><a href="{$v.url}" {if $page_data['page'] eq $v['num']}class="active"{/if}>{$v.num}</a></li>
    {/volist}
```

#### 下一页
```html
    {if $page_data['next_page']}<li><a href="{$page_data.next_page.url}"><span class="fa fa-angle-right"></span></a></li>{/if}
```

### 详情页面
#### 上一篇:
```html
    {if $content.prev}<ul class="fot_prv com_url" data-url="{$content.prev.url}">上一篇：{$content.prev.title}</ul>{/if}
```
#### 下一篇：
```html
    {if $content.next}<ul class="fot_next com_url" data-url="{$content.next.url}">下一篇：{$content.next.title}</ul>{/if}
```


### 非单页面获取单页数据：
```html
    <!-- 注意52是单页 栏目id  type为字段-->
    {tp:page id="52" type="content"}
```

### 不放a标签点击跳转页面（确定✅页面里有引用过jquery）
```js
    <script>
        $('.com_url').on('click',function(e){
            var url = $(this).attr('data-url');
            location.href = url;
        });
    </script>
```



### 友情链接
```html
        <!-- 友情链接1 -->
        {tp:link  limit="20"}
            <li><a href="{$link.url}">{$key+1}.{$link.title}</a></li>
        {/tp:link}

        <!-- 友情链接2 -->
        {tp:link  name="v"}
            <li><a href="{$v.url}">{$v.title}</a></li>
        {/tp:link}

        <!-- 友情链接3 -->
        <ul class="list">
            {tp:link  limit="20" }
            <li><a href="{$link.url}" onclick="sendLinkClickRequestAndOpenLink(event, {$link.id}); return false;">{$key+1}.{$link.title}</a></li>
            {/tp:link}
        </ul>

        <script>
            // 这个函数会在用户点击链接时被调用
            function sendLinkClickRequestAndOpenLink(event, id) {
                // 发送请求到服务器
                fetch('/api/cms/link_click?id='+id)
                    .then(response => {
                        // 检查请求是否成功
                        if (response.ok) {
                            // console.log('请求发送成功');
                        } else {
                            // console.error('请求发送失败');
                        }
                    })
                    .catch(error => {
                        // 处理网络错误
                        // console.error('网络错误：', error);
                    });
                // 打开链接在新窗口
                window.open(event.target.href, '_blank');
            }
        </script>

```

### 其他说明
```html
    留言地址: /cms/gbook/add 前段页面form表单提交地址可以返回

    <!-- text验证码示例 建议判断加上-->
    {if $site.gbook_captcha_switch}
    <div class="col-md-6 col-sm-6 col-xs-12 form-group">
        <input type="text" name="captcha" placeholder="请输验证码" required>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-12 form-group">
        <img src="{:captcha_src()}" style="height: 100%;" onclick="this.src = '{:captcha_src()}?r=' + Math.random();"/>
    </div>
    {/if}


    获取当前位置信息
    <!-- cate_id 注意栏目页是$cate  详情页是$content-->
    <span class="inposition">当前位置：</span><a href="/">首页</a>
    {tp:position name="v" cate_id="$cate['id']"}
    &gt; <a class="navover" href="{$v.url}"> {$v.name}</a>
    {/tp:position}

    🌹除了首页都有$cate变量(可直接使用)
    🌹详情页和单页有$content变量(可直接使用)
    🌹搜索地址:/路由?search=搜索的内容 自动搜索该栏目下的内容
      示例/news?search=小和
      搜索代码application/cms/controller/Cms.php get_cate_art_list2方法
        if(isset($params['search']) && !empty($params['search'])){
            $where['title|content|seotitle|keywords|description|memo'] = ['like',"%".$params['search']."%"];
            unset($where['cate_id']);//如果搜索当前栏目下的列表就注释该代码
        }

```

```html
    data-area='["80%", "80%"]' 按钮弹窗大小定义
    示例
    <a href="javascript:;" class="btn btn-success btn-add  title="{:__('Add')}" data-area='["80%", "80%"]' ><i class="fa fa-plus"></i> {:__('Add')}</a>
```

```
    自己添加定时检查链接是否有效
    域名+/api/cms/link_check 检测链接是否有效
```
## 版本更新日志
    2024-03-01 开始立项
    2024-05-13 继续开始开发
    2024-05-16 完成基本功能
    2024-05-17 添加违禁词验证(后台内容可强制添加/编辑 有设置开关,函数bans(违禁词)返回包含的违禁词) 
    2024-05-22 添加后台超级管理员配置 可隐藏非超级管理员的组或者某个配置字段
    2024-05-30 添加后台配置字段crud_copy_switch 可在线命令生成 复制数据按钮
    2024-11-28 添加批量移动/复制/编辑字段,根据表含有cate_id自动生成 (方法在👉🏻app\common\controller\Backend)
    2024-11-29 添加前台网站生成sitemap功能 /api/cms/update_sitemap




## 代码说明

## 文件说明

* 标签文件定义
application/common/library/Tp.php

* 方法定义
application/common.php 





* 栏目选择 把空格实体替换成空字符
text = text.replace(/&nbsp;/g," ");
文件路径
public/assets/libs/fastadmin-selectpage/selectpage.js
## 问题反馈

在使用中有任何问题，请加QQ群153073132 请备注CMS

## 特别鸣谢
FastAdmin

## 版权信息

CMS遵循Apache2开源协议发布，并提供免费使用。
本项目包含的第三方源码和二进制文件之版权信息另行标注。
