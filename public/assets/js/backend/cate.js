define(['jquery', 'bootstrap', 'backend', 'table', 'form', 'template', 'editable'], function ($, undefined, Backend, Table, Form, Template, undefined) {
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }
    var default_lang = getCookie('default_lang');//获取cookie中的默认语言

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'cate/index' + location.search,
                    add_url: 'cate/add',
                    edit_url: 'cate/edit',
                    del_url: 'cate/del',
                    multi_url: 'cate/multi',
                    import_url: 'cate/import',
                    table: 'cate',
                }
            });


           

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                fixedColumns: true,
                fixedRightNumber: 1,

                escape: false,
                pagination: false,
                search: false,
                commonSearch: false,
                // rowAttributes: function (row, index) {
                //     return row.pid == 0 ? {} : {style: "display:none"};
                // },
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'name', title: __('Name'), operate: 'LIKE', align: 'left', formatter: Controller.api.formatter.title, clickToSelect: !false },
                        // {field: 'byname', title: __('Byname'), operate: 'LIKE'},

                        { field: 'type', title: __('Type'), searchList: { "page": __('Type page'), "link": __('Type link'), "list": __('Type list') }, formatter: Table.api.formatter.normal },
                        // {field: 'model_id', title: __('Model_id')},
                        { field: 'parent_id', title: __('Parent_id') },
                        { field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image },
                        { field: 'flag', title: __('Flag'), operate: 'LIKE', formatter: Table.api.formatter.flag },
                        // {field: 'seotitle', title: __('Seotitle'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'keywords', title: __('Keywords'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'description', title: __('Description'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        { field: 'outlink', title: __('Outlink'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content },
                        { field: 'items', title: __('Items'), formatter: Controller.api.formatter.newsList },

                        { field: 'weigh', title: __('Weigh'), operate: false, editable: true },
                        // {field: 'tpl', title: __('Tpl'), operate: 'LIKE'},
                        { field: 'listtpl', title: __('Listtpl'), operate: 'LIKE' },
                        { field: 'showtpl', title: __('Showtpl'), operate: 'LIKE' },
                        { field: 'pagesize', title: __('Pagesize') },
                        { field: 'isnav_switch', title: __('Isnav_switch'), searchList: { "1": __('Yes'), "0": __('No') }, table: table, formatter: Table.api.formatter.toggle },



                        { field: 'fanyi_switch', title: __('fanyi_switch'), searchList: { "1": __('Yes'), "0": __('No') }, table: table, formatter: Table.api.formatter.toggle ,visible: function (row) {
                                if (row.lang == default_lang) {
                                    return false; //隐藏该按钮
                                } else {
                                    return true; //显示该按钮
                                }
                            }
                        
                        },


                        { field: 'status', title: __('Status'), searchList: { "1": __('Status 1'), "2": __('Status 2') }, formatter: Table.api.formatter.status },
                        { field: 'diyname', title: __('Diyname'), operate: 'LIKE' },
                        { field: 'table_name', title: __('Table_name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content },
                        { field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        { field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime },
                        {
                            field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate, buttons: [

                                {
                                    name: 'fanyi',
                                    text: __('fanyi'),
                                    title: function (row) {
                                        return __('fanyi');
                                    },

                                    hidden: function (row) {
                                        if (row.lang == default_lang) {//支付状态:1=待支付,2=已支付,3=已过期
                                            return false; //显示该按钮
                                        } else {
                                            return true; //隐藏该按钮
                                        }
                                    },
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    icon: 'fa fa-language',
                                    url: 'api/fanyi_cate?ids={id}',
                                    success: function (data, ret) {
                                        Layer.msg(ret.msg);
                                        $(".btn-refresh").trigger("click");
                                    },
                                    error: function (err) {
                                        console.log(err);
                                    }

                                },


                                {
                                    name: 'add',
                                    text: __('add') + "子栏目",
                                    title: function (row) {
                                        return row.name + "下面添加子栏目";
                                    },


                                    classname: 'btn btn-xs btn-primary btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: 'cate/add?parent_id={id}',
                                    callback: function (data) {
                                        Fast.api.close(data);
                                    },
                                    //窗口区域定义
                                    extend: 'data-area=\'["50%", "95%"]\'',

                                },






                                {
                                    name: 'look',
                                    text: '查看页面',
                                    title: function (row) {
                                        return "查看页面：" + row.name;
                                    },
                                    classname: 'btn btn-xs btn-info btn-addtabs',
                                    icon: 'fa fa-pages',
                                    url: '/{diyname}',
                                },

                            ]
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


            var btnSuccessEvent = function (data, ret) {
                if ($(this).hasClass("btn-change")) {
                    var index = $(this).data("index");
                    var row = Table.api.getrowbyindex(table, index);
                    row.ismenu = $("i.fa.text-gray", this).length > 0 ? 1 : 0;
                    table.bootstrapTable("updateRow", { index: index, row: row });
                } else if ($(this).hasClass("btn-delone")) {
                    if ($(this).closest("tr[data-index]").find("a.btn-node-sub.disabled").length > 0) {
                        $(this).closest("tr[data-index]").remove();
                    } else {
                        table.bootstrapTable('refresh');
                    }
                } else if ($(this).hasClass("btn-dragsort")) {
                    table.bootstrapTable('refresh');
                }
                Fast.api.refreshmenu();
                return false;
            };
            //表格内容渲染前
            table.on('pre-body.bs.table', function (e, data) {
                var options = table.bootstrapTable("getOptions");
                options.escape = true;
            });
            //当内容渲染完成后
            table.on('post-body.bs.table', function (e, data) {
                var options = table.bootstrapTable("getOptions");
                options.escape = false;

                //点击切换/排序/删除操作后刷新左侧菜单
                $(".btn-change[data-id],.btn-delone,.btn-dragsort").data("success", btnSuccessEvent);

            });

            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                //显示隐藏子节点
                $(">tbody>tr[data-index] > td", this).on('click', "a.btn-node-sub", function () {
                    var status = $(this).data("shown") ? true : false;
                    $("a[data-pid='" + $(this).data("id") + "']").each(function () {
                        $(this).closest("tr").toggle(!status);
                    });
                    if (status) {
                        $("a[data-pid='" + $(this).data("id") + "']").trigger("collapse");
                    }
                    $(this).data("shown", !status);
                    $("i", this).toggleClass("fa-caret-down").toggleClass("fa-caret-right");
                    return false;
                });
            });

            //隐藏子节点
            $(document).on("collapse", ".btn-node-sub", function () {
                if ($("i", this).length > 0) {
                    $("a[data-pid='" + $(this).data("id") + "']").trigger("collapse");
                }
                $("i", this).removeClass("fa-caret-down").addClass("fa-caret-right");
                $(this).data("shown", false);
                $(this).closest("tr").toggle(false);
            });

            //批量删除后的回调
            $(".toolbar > .btn-del,.toolbar .btn-more~ul>li>a").data("success", function (e) {
                Fast.api.refreshmenu();
            });

            //展开隐藏一级
            $(document.body).on("click", ".btn-toggle", function (e) {
                $("a[data-id][data-pid][data-pid!=0].disabled").closest("tr").hide();
                var that = this;
                var show = $("i", that).hasClass("fa-chevron-down");
                $("i", that).toggleClass("fa-chevron-down", !show).toggleClass("fa-chevron-up", show);
                $("a[data-id][data-pid][data-pid!=0]").not('.disabled').closest("tr").toggle(show);
                $(".btn-node-sub[data-pid=0]").data("shown", show);
            });

            //展开隐藏全部
            $(document.body).on("click", ".btn-toggle-all", function (e) {
                var that = this;
                var show = $("i", that).hasClass("fa-plus");
                $("i", that).toggleClass("fa-plus", !show).toggleClass("fa-minus", show);
                $(".btn-node-sub:not([data-pid=0])").closest("tr").toggle(show);
                $(".btn-node-sub").data("shown", show);
                $(".btn-node-sub > i").toggleClass("fa-caret-down", show).toggleClass("fa-caret-right", !show);
            });
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'cate/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        { checkbox: true },
                        { field: 'id', title: __('Id') },
                        { field: 'name', title: __('Name'), align: 'left' },
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '140px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'cate/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'cate/destroy',
                                    refresh: true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },

        add: function () {
            function hide_all() {
                $(".div-outlink").hide();
                $(".div-page").hide();
                $(".div-list").hide();
            }
            function page_show() {
                hide_all();
                $(".div-page").show();
            }
            function list_show() {
                hide_all();
                $(".div-list").show();
            }

            function link_show() {
                hide_all();
                $(".div-link").show();

            }
            function show_div(type) {
                switch (type) {
                    case 'page':
                        page_show();
                        break;
                    case 'list':
                        list_show();
                        break;
                    case 'link':
                        link_show();
                        break;
                }
            }
            $(document).on("change", "#c-type", function () {
                //变更后的回调事件
                var type = $(this).val();
                show_div(type);
            });

            Controller.api.bindevent();
            //
            var type = $("#c-type").val();
            show_div(type);

        },
        edit: function () {
            function hide_all() {
                $(".div-outlink").hide();
                $(".div-page").hide();
                $(".div-list").hide();
            }
            function page_show() {
                hide_all();
                $(".div-page").show();
            }
            function list_show() {
                hide_all();
                $(".div-list").show();
            }

            function link_show() {
                hide_all();
                $(".div-link").show();

            }
            function show_div(type) {
                switch (type) {
                    case 'page':
                        page_show();
                        break;
                    case 'list':
                        list_show();
                        break;
                    case 'link':
                        link_show();
                        break;
                }
            }
            $(document).on("change", "#c-type", function () {
                //变更后的回调事件
                var type = $(this).val();
                show_div(type);
            });

            Controller.api.bindevent();
            var type = $("#c-type").val();
            show_div(type);
        },
        api: {
            formatter: {
                newsList: function (value, row, index) {
                    //这里手动构造URL
                    urlChildren = row.table_name.replace(/fa_/g, '') + "/index?cate_id" + "=" + row.ChildrenIds;
                    url = row.table_name.replace(/fa_/g, '') + "/index?cate_id" + "=" + row.id;
                    switch (row.type) {
                        case 'page':
                            return '<a href="page/edit?cate_id=' + row.id + '" class="label label-success addtabsit" style="font-size:90%;" title="' + __("编辑单页 %s", row.name) + '">编辑单页</a>';
                            break;

                        default:
                            break;
                    }

                    //方式一,直接返回class带有addtabsit的链接,这可以方便自定义显示内容
                    if (value > 0) {
                        if (url == urlChildren)
                            return '<a href="' + url + '" class="label label-info addtabsit" style="font-size:90%;" title="' + __("查看列表 %s", row.name) + '">' + value + '</a>';

                        return '<a href="' + urlChildren + '" class="label label-info addtabsit" style="font-size:90%;" title="' + __("查看子列表 %s", row.name) + '">子列表</a> <a href="' + url + '" class="label label-info addtabsit" style="font-size:90%;" title="' + __("查看列表 %s", row.name) + '">' + value + '</a>';
                    } else {
                        if (url == urlChildren)
                            return '<a href="' + url + '" class="label label-blue addtabsit" style="color:green;font-size:90%;" title="' + __("查看列表 %s", row.name) + '">列表</a>';

                        return '<a href="' + urlChildren + '" class="label label-blue addtabsit" style="color:green;font-size:90%;" title="' + __("查看子列表 %s", row.name) + '">子列表</a> <a href="' + url + '" class="label label-blue addtabsit" style="color:green;font-size:90%;" title="' + __("查看列表 %s", row.name) + '">' + value + '</a>';
                    }


                    //方式二,直接调用Table.api.formatter.addtabs
                    // this.url = url;
                    // return Table.api.formatter.addtabs.call(this, value, row, index);
                },
                title: function (value, row, index) {
                    value = value.toString().replace(/(&|&amp;)nbsp;/g, '&nbsp;');
                    var caret = row.haschild == 1 || row.ismenu == 1 ? '<i class="fa fa-caret-right"></i>' : '';
                    value = value.indexOf("&nbsp;") > -1 ? value.replace(/(.*)&nbsp;/, "$1" + caret) : caret + value;

                    value = !row.ismenu || row.status == 'hidden' ? "<span class='text-muted'>" + value + "</span>" : value;
                    return '<a href="javascript:;" data-id="' + row.id + '" data-pid="' + row.pid + '" class="'
                        + (row.haschild == 1 || row.ismenu == 1 ? 'text-primary' : 'disabled') + ' btn-node-sub">' + value + '</a>';
                },
                name: function (value, row, index) {
                    return !row.ismenu || row.status == 'hidden' ? "<span class='text-muted'>" + value + "</span>" : value;
                },
                icon: function (value, row, index) {
                    return '<span class="' + (!row.ismenu || row.status == 'hidden' ? 'text-muted' : '') + '"><i class="' + value + '"></i></span>';
                }
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
