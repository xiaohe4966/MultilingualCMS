define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
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
                    index_url: 'pro/index' + location.search,
                    add_url: 'pro/add',
                    edit_url: 'pro/edit',
                    del_url: 'pro/del',
                    multi_url: 'pro/multi',
                    import_url: 'pro/import',
                    multiedit_url: 'pro/multiedit',
                    table: 'pro',
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
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'fanyi_switch', title: __('Fanyi_switch'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'fanyi_time', title: __('Fanyi_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'diyname', title: __('Diyname'), operate: 'LIKE'},
                        {field: 'cate_id', title: __('Cate_id'), operate:'IN', events: Table.api.events.search},
                        {field: 'title', title: __('Title'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        {field: 'hotdata', title: __('Hotdata'), searchList: {"1":__('Hotdata 1'),"2":__('Hotdata 2'),"3":__('Hotdata 3'),"4":__('Hotdata 4')}, operate:'FIND_IN_SET', formatter: Table.api.formatter.label},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'images', title: __('Images'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'tags', title: __('Tags'), operate: 'LIKE', formatter: Table.api.formatter.flag},
                        {field: 'switch', title: __('Switch'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'cate.name', title: __('Cate.name'), operate: 'LIKE'},
                       
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, buttons: [

                            {
                                name: 'fanyi',
                                text: __('fanyi'),
                                title: function (row) {
                                    return __('fanyi');
                                },

                                hidden: function (row) {
                                    if (row.lang == default_lang) {//
                                        return false; //显示该按钮
                                    } else {
                                        return true; //隐藏该按钮
                                    }
                                },
                                classname: 'btn btn-xs btn-success btn-ajax',
                                icon: 'fa fa-language',
                                url: 'pro/fanyi?ids={id}',
                                success: function (data, ret) {
                                    Layer.msg(ret.msg);
                                    $(".btn-refresh").trigger("click");
                                },
                                error: function (err) {
                                    console.log(err);
                                }

                            },
                            {name: 'copy',title: __('Copy'),text: __('Copy'),classname: 'btn btn-xs btn-info btn-ajax',icon: 'fa fa-copy',url: function(row){return 'pro/copy?ids=' + row.id}}
                        ], formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
                url: 'pro/recyclebin' + location.search,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), align: 'left'},
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
                                    url: 'pro/restore',
                                    refresh: true
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'pro/destroy',
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
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        multiedit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
