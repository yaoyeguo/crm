document.body.style.minHeight = window.innerHeight + 'px';
if ((/MicroMessenger/i).test(window.navigator.userAgent)) {
    // window.addEventListener('WeixinJSBridgeReady', function(e) {
    $('[data-topbar]').hide();
    // }, false);
}

(function(){

    var AreaData;
    var SelectedData = [];
    var ForselectData = [];
    var areaselect = $('#areaselect');

    function perloadAreaData(url, callback) {
        if(!window.AreaData) {
            $.get(DESKTOPRESURL+'/js/area.json', function(data) {
                AreaData = data;
                callback && callback(data);
            });
        }
        else {
            callback && callback(AreaData);
        }
    }
    function buildAreaList(data, dom) {
        var parent = dom || document.querySelector('#areaselect .act-forselect');

        if(parent) {
            parent.innerHTML = buildAreaItems(data);
        }
    }
    function buildAreaItems(data) {
        var dom = '';
        data.forEach(function(item, i) {
            dom += '<li class="cell"' +
                'data-parent-id="' + item.parentId + '" ' +
                'data-value="' + item.value + '" ' +
                'data-id="' + item.id + '" ' +
                'data-isleaf="' + !(item.children && item.children.length) + '">' +
                item.value + '</li>';
        });
        return dom;
    }
    function resetArea() {
        var selected = areaselect.find('.act-selected');
        var forselect = areaselect.find('.act-forselect');
        selected.empty();
        forselect.empty();
        buildAreaList(SelectedData, selected[0]);
        buildAreaList(ForselectData, forselect[0]);
    }
    function bindAreaEvent() {
        $('#area').click(function(e) {
            if(SelectedData.length) {
                resetArea(SelectedData, ForselectData);
            }
            else {
                buildAreaList(AreaData);
            }
            areaselect.show();
        });
        areaselect.on('click', '.act-selected .cell', function(e) {
            var parentId = $(this).data('parentId');
            var isleaf = $(this).data('isleaf');
            if(parentId == 1) {
                $(this).parent().empty();
                SelectedData = [];
                ForselectData = [];
                buildAreaList(AreaData);
            }
            else {
                $(this).remove();
                SelectedData.forEach(function(item, i) {
                    if(item.id == parentId) {
                        SelectedData.pop();
                        ForselectData = item.children;
                        buildAreaList(ForselectData);
                    }
                    return false;
                });
            }
        })
        .on('click', '.act-forselect .cell', function(e) {
            var id = $(this).data('id');
            var isleaf = $(this).data('isleaf');
            $(this).clone(true).appendTo(areaselect.find('.act-selected'));

            if(isleaf === false) {
                var data = ForselectData.length ? ForselectData : AreaData;
                data.forEach(function(item, i) {
                    if(item.id == id) {
                        SelectedData.push(item);
                        ForselectData = item.children;
                        buildAreaList(ForselectData);
                    }
                    return false;
                });
            }
            else {
                // $(this).parent().empty();
                areaselect.hide();
                var id = [];
                var value = [];
                areaselect.find('.act-selected .cell').each(function(index, el) {
                    id.push($(el).data('id'));
                    value.push($(el).data('value'));
                });
                $('#area span').text(value.join(''));
                $('#area input').val(id.join('/') + ':' + value.join('/'));
            }
        });
    }

    if($('#area').length) {
        perloadAreaData($('#area').data('url'));
        bindAreaEvent();
    }

})();

// 通用倒计时，包括倒计时所在容器，倒数秒数，显示方式，回调。
function countdown(element, options){
    var self = this;
    options = $.extend({
        start: 60,
        secondOnly: false,
        callback: null
    }, options || {});
    var t = options.start;
    var sec = options.secondOnly;
    var fn = options.callback;
    var d = +new Date();
    var diff = Math.round((d + t * 1000) / 1000);
    this.timer = timeout(element, diff, fn);
    this.stop = function() {
        clearTimeout(self.timer);
    };

    function timeout(element, until, fn) {
        var str = '',
            started = false,
            left = {d: 0, h: 0, m: 0, s: 0, t: 0},
            current = Math.round(+new Date() / 1000),
            data = {d: '天', h: '时', m: '分', s: '秒'};

        left.s = until - current;

        if (left.s < 0) {
            return;
        }
        else if(left.s == 0) {
            fn && fn();
        }
        if(!sec) {
            if (Math.floor(left.s / 86400) > 0) {
              left.d = Math.floor(left.s / 86400);
              left.s = left.s % 86400;
              str += left.d + data.d;
              started = true;
            }
            if (Math.floor(left.s / 3600) > 0) {
              left.h = Math.floor(left.s / 3600);
              left.s = left.s % 3600;
              started = true;
            }
        }
        if (started) {
          str += ' ' + left.h + data.h;
          started = true;
        }
        if(!sec) {
            if (Math.floor(left.s / 60) > 0) {
              left.m = Math.floor(left.s / 60);
              left.s = left.s % 60;
              started = true;
            }
        }
        if (started) {
          str += ' ' + left.m + data.m;
          started = true;
        }
        if (Math.floor(left.s) > 0) {
          started = true;
        }
        if (started) {
          str += ' ' + left.s + data.s;
          started = true;
        }

        $(element).html(str);
        return setTimeout(function() {timeout(element, until,fn);}, 1000);
    }
}
