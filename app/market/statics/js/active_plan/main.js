;(function (win, $, factory) {
    win.CrmJS = factory($);
})(window, jQuery, function ($) {
    //类似amd的模块编程模型
    var events = {}; //事件集合
    var iconCount = 0; //添加到页面的icon总数
    var flagParent = ''; //分支父级标示

    var res = {
        util: {
            //注册事件
            registerEvt: function (evtName, fn) {
                var eventEntry,
                    fnEntry,
                    index = 0;

                if (typeof evtName === 'function') {
                    fn = evtName;
                    evtName = 'click';
                }

                if (fn && Array.isArray(fn)) {
                    while (fnEntry = (fn[index++])) {
                        this.registerEvt(evtName, fnEntry);
                    }
                } else {
                    eventEntry = events[evtName] || (events[evtName] = []);
                    eventEntry.push(fn);
                }
            },

            //根据事件获取对应的响应方法
            getEvtFn: function (evtName) {
                return events[evtName] || undefined;
            },

            //模板生成
            buildTemplate: function (txt) {
                var isSelector = /^[#.]([\w-]+)/,
                    htmlContent = isSelector.test(txt) ? $(txt).html() : txt;

                return function (data) {
                    return htmlContent.replace(/<%=\s*([\w-]+)%>/gi, function (v, v1) {
                        return data[v1] || '';
                    })
                }
            },

            //对象合并(是否覆盖)
            mix: function (target, source) {
                var args = Array.prototype.slice.call(arguments),
                    ride = typeof args[args.length - 1] === 'boolean' ?
                        args[args.length - 1] :
                        true,
                    index = 1, prop, native;

                if (args.length === 1) {
                    source = target;
                    target = this;
                }

                for (; index < args.length; index += 1) {
                    native = args[index];
                    for (prop in native) {
                        if (Object.prototype.hasOwnProperty.call(native, prop)) {
                            (ride || !(target[prop])) && (target[prop] = native[prop]);
                        }
                    }
                }
                return target;
            }
        },

        model: {
            bgColor: ['#9ba5b5', '#b59bb0', '#a1b59b', '#b5ac9b', '#c99191', '#9bb4b5', '#a29bb5'],
            template: {
                'activeTime': '#active_time_template', //活动时间模板ID
                'waitTime': '#wait_time_template', //等待时间模板ID
                'tj': '#screen_template', //筛选条件模板ID
                'dx': '#dx_template', //发送短信模板ID
                'group': '#group_template', //统计分组模板ID
                'menu': '#menu_template' //菜单模板ID
            }
        },

        action: {
            /*
             * 为DOM对象绑定事件
             * @params isOnlyOption {boolean} 是否是根元素
             * @params isRmPrev {boolean} 是否删除之前的对象
             * @params ele {HTMLElement} DOM对象
             * @params evtType {string} 采取什么类型绑定事件
             * @params evtName {String} 事件名
             * @params evtIndex {Number} 事件所代表的业务逻辑类别
             * @params templateType {string} 映射的模板名
             * @params parentEle {HTMLElement} 父容器
             * @params initTop {Number} 初始化纵坐标
             * */
            bind: function (isOnlyOption, isRmPrev, ele, evtType, evtName, evtIndex, parentEle, templateMes, initTop) {
                var handler = res.util.getEvtFn(evtName)[evtIndex],
                    isDomParent;

                if (isOnlyOption === true) {
                    ele[evtType](evtName, function (e) {
                        handler($(this));
                    });
                } else if (isOnlyOption === 'del') {
                    ele[evtType](evtName, function (e) {
                        var $iconParent, style = '';
                        if ($(this).parents('.icon-box').hasClass('dx') || $(this).parents('.icon-box').hasClass('tj')) {
                            $iconParent = $(this).parent().parent().parent();
                            style = $(this).parents('.icon-box').hasClass('dx') ? '.dx' : '.tj';
                            handler('branch', $iconParent, $iconParent.data('flag').slice(0, -2), style);
                        } else {
                            $iconParent = $(this).parents('.icon-box');
                            handler('nobranch', $iconParent);
                        }
                        e.stopPropagation();
                    })
                }

                else if (isOnlyOption === 'edit') {
                    ele[evtType](evtName, function (e) {
                        handler(function () {

                        });
                        e.stopPropagation();
                    })
                }

                else if (evtName === 'click') {
                    ele[evtType](evtName, function (e) {

                        isDomParent = $(this).data('parent') === 'menus' ? false : true;
                        res.action.show(isDomParent, $(this), parentEle, templateMes, initTop, handler);
                        e.stopPropagation();
                    });
                } else if (evtName === 'mouseover') {
                    ele[evtType](evtName, function (e) {
                        handler($(this));
                        e.stopPropagation();
                    });
                } else if (evtName === 'mouseleave') {
                    ele[evtType](evtName, function (e) {
                        handler($(this));
                        e.stopPropagation();
                    });
                }
            },

            ajaxSend: function (url, callback) {
                $.getJSON(url, function (data) {
                    callback(data);
                })
            },

            concatStr: function (data) {
                var i = 0, size = data.length, resStr = '<ul>';
                for (; i < size; i += 1) {
                    resStr += '<li data-val="'+ data[i]['val']+'" data-url="'+ data[i]['url'] +'" data-parent="menus"><a href="javascript:;">'+data[i]['text']+'</a></li></ul>';
                }
                return resStr;
            },

            concatTimeStr : function (data) {
                var resStr = '<ul><li>开始时间</li><li><p class="date">'+data['date']+'</p><p class="tim">'+data['time']+'</p></li><li><i class="exp">每'+data['every']['day']+'天'+data['every']['count']+'次</i></li></ul>';
                return resStr;
            },

            concatPhoneStr: function () {
                var resStr = '';
                    resStr += '<div class="content clearfix" data-flag="<%= space%>">';
                    resStr += '<div><div class="prop"><i class="dire zs"></i><span class="txt"><%= flag%></span><span class="flag-box only-dire sdx"></span><i class="del"></i><i class="edit"></i></div><div class="val inner"><p>结果</p><p><%= total%></p><i class="num-icon"></i></div><a href="javascript:;" class="add-icon" data-parent="icon-box"><span>＋</span></a></div></div>';
                return resStr;
            },

            //调用dialog方法
            callDialog: function (dialogFn, dialogUrl, fn) {
                /*调用系统dialog方法*/
                dialogFn(dialogUrl, fn);
            },

            //图标展示
            show: function (isDomParent, nativeEle, parentEle, templateMes, initTop, handler) {
                var bgColorFlag, templateType, templateArr, templateStr,
                    branchCount, parentFlag,
                    left, top,
                    i = 0, data = [];


                /* 点击＋，添加菜单项 */
                if (isDomParent) {

                   /*
                   * 显示菜单项
                   * 后台返回的参数格式
                   * [
                   *   {
                   *     url 对话框地址
                   *     val 指定为哪种菜单项和颜色 activeTime.1
                   *     text 菜单项显示文字
                   *   }
                   * ]
                   *
                   *
                   * */

                   this.ajaxSend('/menus', function (menus) {
                       templateArr = templateMes;
                       templateType = templateArr.split('.')[0];
                       bgColorFlag = templateArr.split('.')[1];
                       templateStr = res.util.buildTemplate(res.model.template[templateType]);


                       //得到当前元素的x,y坐标，以便赋予之后创建的对象
                       left = nativeEle.offset().left - 6;
                       top = isDomParent === true ? nativeEle.parent().parent().offset().top - parentEle.offset().top - 5:
                           nativeEle.parents('.' + nativeEle.data('parent')).size() > 1 ?
                               nativeEle.parent().parent().position().top :
                               nativeEle.parents('.' + nativeEle.data('parent')).position().top;

                       left += res.action.fetchScrollLeft($('.area'));
                       top += res.action.fetchScrollTop($('.area'));
                       branchCount = 0;

                       if ((nativeEle.parents('.icon-box').hasClass('dx') || nativeEle.parents('.icon-box').hasClass('tj')) && nativeEle.attr('class') === 'add-icon') {
                           if (nativeEle.parents('.' + nativeEle.data('parent')).size() > 1) {
                               flagParent = nativeEle.parent().parent().data('flag');
                           } else {
                               flagParent = nativeEle.parents('.content').data('flag');
                           }
                       }

                       if (templateType === 'dx' || templateType === 'tj') {
                           left = nativeEle.offset().left + 20;
                           for (i; i < branchCount; i += 1) {
                               data.push({
                                   space: flagParent + i + '-'
                               });
                           }
                       }
                       //触发注册的事件
                       handler(left, top, templateStr, parentEle, nativeEle.parents('.' + nativeEle.data('parent')).size() > 1 ? nativeEle.parent().parent(): nativeEle.parents('.' + nativeEle.data('parent')), res.action.appendBgColor(bgColorFlag), ((templateType === 'dx' || templateType === 'tj' ) ? branchCount : 0), templateType, data, res.action.concatStr(menus));

                       if (!templateMes) {
                           iconCount = parentEle.children('.icon-box').size() - 1;
                       }

                       //删除当前显示的DOM对象
                       res.action.remove(isDomParent, nativeEle);

                   });
                   return;
                }
                var handle = null, self = this;
                /*
                 * 显示等待时间或者活动时间具体项
                 * 后台返回的参数格式
                 * [
                 *   {
                 *     date 开始日期 yyyy-mm-dd
                 *     time 开始时间 HH:ii:ss
                 *     every : {
                 *        day: 每多少天
                 *        count: 每多少次
                 *     }
                 *   }
                 * ]
                 *
                 *
                 * */
                if (nativeEle.data('val').indexOf('activeTime') === 0 || nativeEle.data('val').indexOf('waitTime') === 0) {
                    handle = function () {
                        self.ajaxSend('/activeTime', function (data) {
                            templateArr = nativeEle.data('val');
                            templateType = templateArr.split('.')[0];
                            bgColorFlag = templateArr.split('.')[1];
                            templateStr = res.util.buildTemplate(res.model.template[templateType]);


                            //得到当前元素的x,y坐标，以便赋予之后创建的对象
                            left = nativeEle.offset().left - 6;
                            top = isDomParent === true ? nativeEle.parent().parent().offset().top - parentEle.offset().top - 5:
                                nativeEle.parents('.' + nativeEle.data('parent')).size() > 1 ?
                                    nativeEle.parent().parent().position().top :
                                    nativeEle.parents('.' + nativeEle.data('parent')).position().top;

                            left += res.action.fetchScrollLeft($('.area'));
                            top += res.action.fetchScrollTop($('.area'));
                            branchCount = 0;

                            if ((nativeEle.parents('.icon-box').hasClass('dx') || nativeEle.parents('.icon-box').hasClass('tj')) && nativeEle.attr('class') === 'add-icon') {
                                if (nativeEle.parents('.' + nativeEle.data('parent')).size() > 1) {
                                    flagParent = nativeEle.parent().parent().data('flag');
                                } else {
                                    flagParent = nativeEle.parents('.content').data('flag');
                                }
                            }

                            if (templateType === 'dx' || templateType === 'tj') {
                                left = nativeEle.offset().left + 20;
                                for (i; i < branchCount; i += 1) {
                                    data.push({
                                        space: flagParent + i + '-'
                                    });
                                }
                            }
                            //触发注册的事件
                            handler(left, top, templateStr, parentEle, nativeEle.parents('.' + nativeEle.data('parent')).size() > 1 ? nativeEle.parent().parent(): nativeEle.parents('.' + nativeEle.data('parent')), res.action.appendBgColor(bgColorFlag), ((templateType === 'dx' || templateType === 'tj' ) ? branchCount : 0), templateType, data, res.action.concatTimeStr(data));

                            if (!templateMes) {
                                iconCount = parentEle.children('.icon-box').size() - 1;
                            }

                            //删除当前显示的DOM对象
                            res.action.remove(isDomParent, nativeEle);
                        });
                    }
                }

                /*
                 * 筛选条件和发送短信
                 * 后台返回的参数格式
                 * [
                 *   {
                 *     total 人数 3000
                 *   }
                 * ]
                 *
                 *
                 * */

                if (nativeEle.data('val').indexOf('tj') === 0 || nativeEle.data('val').indexOf('dx') === 0) {
                    handle = function () {
                        self.ajaxSend('/tj', function (data) {
                            templateArr = nativeEle.data('val');
                            templateType = templateArr.split('.')[0];
                            bgColorFlag = templateArr.split('.')[1];
                            templateStr = res.util.buildTemplate(res.action.concatPhoneStr());


                            //得到当前元素的x,y坐标，以便赋予之后创建的对象
                            left = nativeEle.offset().left - 6;
                            top = isDomParent === true ? nativeEle.parent().parent().offset().top - parentEle.offset().top - 5:
                                nativeEle.parents('.' + nativeEle.data('parent')).size() > 1 ?
                                    nativeEle.parent().parent().position().top :
                                    nativeEle.parents('.' + nativeEle.data('parent')).position().top;

                            left += res.action.fetchScrollLeft($('.area'));
                            top += res.action.fetchScrollTop($('.area'));
                            branchCount = data.length;

                            if ((nativeEle.parents('.icon-box').hasClass('dx') || nativeEle.parents('.icon-box').hasClass('tj')) && nativeEle.attr('class') === 'add-icon') {
                                if (nativeEle.parents('.' + nativeEle.data('parent')).size() > 1) {
                                    flagParent = nativeEle.parent().parent().data('flag');
                                } else {
                                    flagParent = nativeEle.parents('.content').data('flag');
                                }
                            }

                            if (templateType === 'dx' || templateType === 'tj') {

                                left = nativeEle.offset().left + 20;
                                for (i; i < branchCount; i += 1) {
                                    data[i]['flag'] = nativeEle.data('val').indexOf('tj') === 0 ? '筛选条件' : '发送短信';
                                    data[i]['space'] = flagParent + i + '-';
                                }
                            }
                            //触发注册的事件
                            handler(left, top, templateStr, parentEle, nativeEle.parents('.' + nativeEle.data('parent')).size() > 1 ? nativeEle.parent().parent(): nativeEle.parents('.' + nativeEle.data('parent')), res.action.appendBgColor(bgColorFlag), ((templateType === 'dx' || templateType === 'tj' ) ? branchCount : 0), templateType, data);

                            if (!templateMes) {
                                iconCount = parentEle.children('.icon-box').size() - 1;
                            }

                            //删除当前显示的DOM对象
                            res.action.remove(isDomParent, nativeEle);
                        });
                    }

                }

                //调用系统对话框方法
                this.callDialog(function (fn) {
                    fn();
                }, handle)
            },

            //删除上一次点击的元素
            remove: function (isDomParent, nativeEle) {
                if (!isDomParent) {
                    nativeEle.parents('.' + nativeEle.data('parent')).remove();
                } else {
                   nativeEle.hide();
                }
            },

            //给元素追加背景色
            appendBgColor: function (bgColorFlag) {
                return res.model.bgColor[bgColorFlag];
            },

            fetchScrollTop: function (ele) {
                return ele.scrollTop();
            },

            fetchScrollLeft: function (ele) {
                return ele.scrollLeft();
            },

            //创建多分支icon
            buildMultiBranch: function () {

            },

            //获取根据属性选择器元素的个数
            fetchPropSelector: function (flag, propExp) {

                return flag === 'all' ? ($('[data-flag^="'+propExp+'"]')) : ($('[data-flag="'+propExp+'"]'));
            },

            fetchBranchCount: function () {
                //模拟获取创建分支的个数
                var countArr = [2, 3];
                return countArr[Math.floor(Math.random() * countArr.length)];
            }
        }
    }
    return res;
});