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
                    index_url: 'dbconfig/index' + location.search,
                    add_url: 'dbconfig/add',
                    edit_url: 'dbconfig/edit',
                    del_url: 'dbconfig/del',
                    multi_url: 'dbconfig/multi',
                    import_url: 'dbconfig/import',
                    multiedit_url: 'dbconfig/multiedit',
                    table: 'dbconfig',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'group', title: __('Group'), operate: 'LIKE'},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'tip', title: __('Tip'), operate: 'LIKE'},
                        {field: 'lang', title: __('Lang')},
                        {field: 'fanyi_switch', title: __('Fanyi_switch'), searchList: {"1":__('Yes'),"0":__('No')}, table: table, formatter: Table.api.formatter.toggle},
                        {field: 'fanyi_time', title: __('Fanyi_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'copy_id', title: __('Copy_id')},
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
                                url: 'dbconfig/fanyi?ids={id}',
                                success: function (data, ret) {
                                    Layer.msg(ret.msg);
                                    $(".btn-refresh").trigger("click");
                                },
                                error: function (err) {
                                    console.log(err);
                                }

                            }
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
