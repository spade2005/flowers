function normal_submit(f){
    let form=$(f);
    let url=form.attr("action");
    let data=form.serialize();
    $.post(url,data,function (j){
        if(j.code == '1'){
            showMsg(j.message);
            return;
        }
        showMsg({
            str: j.message,t:2, cb: function () {
                location.reload();
            }
        });
    },'json');
    return false;
}

function showMsg(data) {
    var config = {title: "提示", msg: "操作成功,返回列表页！", timeout: 2500, cb: null};
    if (typeof data == "string") {
        config.msg = data;
    } else {
        if (data.title) config.title = data.title;
        if (data.msg) config.msg = data.msg;
        if (data.timeout) config.timeout = data.timeout;
        if (data.cb) config.cb = data.cb;
    }
    var m = $('#errModal');
    m.find(".modal-title").html(config.title);
    m.find(".modal-body").html(config.msg);
    m.modal('show');
    m.one('hidden.bs.modal', function () {
        config.cb && config.cb();
    });
}

function createTable() {
    if (typeof global_tables == "undefined" || typeof global_tables["open"] == "undefined") {
        return;
    }
    if (!global_tables["open"]) {
        return;
    }
    let obj = {
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: global_tables["ajax"],
            data: function (d) {
                delete d.columns;
                delete d.search;
                delete d.draw;
                global_tables["filterData"] && global_tables["filterData"](d)
            }
        },
        "columns": global_tables["columns"],
        "aoColumns": global_tables["aoColumns"],
        "deferRender": true,
        "searching": false,
        "ordering": false,
        "language": {
            // "lengthMenu": "显示 _MENU_ 条记录",
            "lengthMenu": '显示 <select>' +
                '<option value="10">10</option>' +
                '<option value="20">20</option>' +
                '<option value="30">30</option>' +
                '<option value="40">40</option>' +
                '<option value="50">50</option>' +
                '</select> 记录',
            // "info":           "Showing _START_ to _END_ of _TOTAL_ entries",
            "info": "第_PAGE_页(共_PAGES_页）计_TOTAL_条",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "paginate": {
                "first": "首页",
                "last": "尾页",
                "next": "下页",
                "previous": "上页"
            },
        },

    };
    if (global_tables["_obj"]) {
        global_tables["_obj"].destroy();
    }
    global_tables["_obj"] = $('#' + global_tables["id"]).DataTable(obj);
}


function delData(o, cb) {
    var r = confirm("请确认执行删除!");
    if (r == false) {
        return false;
    }
    var fo = $(o);
    var data = {
        id: fo.data("id")
    }
    $.post(fo.data("url"), data, function (r) {
        if (r.code !== 0) {
            showMsg(r.message);
            return;
        }
        showMsg({
            msg: "操作成功！",
            cb: function () {
                if (cb) {
                    cb();
                }
                createTable();
            }
        });
    }, 'json');
}