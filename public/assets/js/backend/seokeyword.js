/*
 * @Author: he4966
 */
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
                    index_url: 'seokeyword/index' + location.search,
                    add_url: 'seokeyword/add',
                    edit_url: 'seokeyword/edit',
                    del_url: 'seokeyword/del',
                    multi_url: 'seokeyword/multi',
                    import_url: 'seokeyword/import',
                    multiedit_url: 'seokeyword/multiedit',
                    table: 'seokeyword',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE', table: table, class: 'autocontent', formatter: Table.api.formatter.content},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
                                url: 'seokeyword/fanyi?ids={id}',
                                success: function (data, ret) {
                                    Layer.msg(ret.msg);
                                    $(".btn-refresh").trigger("click");
                                },
                                error: function (err) {
                                    console.log(err);
                                }

                            },
                            {name: 'copy',title: __('Copy'),text: __('Copy'),classname: 'btn btn-xs btn-info btn-ajax',icon: 'fa fa-copy',url: function(row){return 'seokeyword/copy?ids=' + row.id}}
                        ], formatter: Table.api.formatter.operate}
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
