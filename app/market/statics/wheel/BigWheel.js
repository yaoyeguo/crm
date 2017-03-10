var Wheel = function(e, a, f) {
    var c = this;
    c.options = {
        color: ["#DD3717", "#FFE552"],
        borderColor: ["#891701", "#E6A50A"],
        fontColor: ["#FFFFFF", "#000000"],
        nothing: "#8F8F8F",
        speed: 3
    };
    var g = function() {
        c.options = $.extend(c.options, f);
        c.awards = {};
        b();
        d()
    };
    var b = function() {
        var h = {},
        i = $(e).width();
        if (i == 0) {
            i = 320
        }
        h.node = document.createElement("canvas");
        h.context = h.node.getContext("2d");
        h.node.width = i - 20;
        h.node.height = i - 20;
        h.node.style.margin = "0 auto";
        e.appendChild(h.node);
        c.canvas = h
    };
    c.rotate = function() {
        var h = $(c.canvas.node);
        var i = 0;
        c.time = setInterval(function() {
            h.css({
                transform: "rotate(" + i + "deg)",
                "-webkit-transform": "rotate(" + i + "deg)",
                "-moz-transform": "rotate(" + i + "deg)"
            });
            i = i + c.options.speed
        },
        15)
    };
    c.stopRotate = function(h, l) {
        var i = a.length,
        n = (1 / i) * 360 / 2;
        if (c.time) {
            clearInterval(c.time);
            var o = $(c.canvas.node),
            m = 'webkitTransform' in document.body.style ? c.canvas.node.style.webkitTransform : 'mozTransform' in document.body ? c.canvas.node.style.mozTransform : c.canvas.node.style.transform,
            j = 0 - c.awards[h],
            q = m.match(/\d+/)[0] - 0,
            p = 360 - q % 360;
            j = (360 * 3) + (j - 90) + n + q + p;
            var k = setInterval(function() {
                if (q < j) {
                    o.css({
                        "transform": "rotate(" + q + "deg)",
                        "-moz-transform": "rotate(" + q + "deg)",
                        "-webkit-transform": "rotate(" + q + "deg)"
                    });
                    q = q + c.options.speed
                } else {
                    clearInterval(k);
                    l && l()
                }
            },
            10)
        }
    };
    var d = function() {
        var h = c.canvas.context,
        l = 0,
        n = a.length,
        m = c.canvas.node.height / 2 - 22;
        h.clearRect(0, 0, c.canvas.node.width, c.canvas.node.height);
        h.fillStyle = "#000000";
        h.globalAlpha = "0.2";
        h.beginPath();
        h.moveTo(c.canvas.node.width / 2, c.canvas.node.height / 2);
        h.arc(c.canvas.node.width / 2, c.canvas.node.height / 2, m + 12, 0, Math.PI / 180 * 360, false);
        h.fill();
        h.globalAlpha = "1";
        h.fillStyle = "#f3b727";
        h.beginPath();
        h.moveTo(c.canvas.node.width / 2, c.canvas.node.height / 2);
        h.arc(c.canvas.node.width / 2, c.canvas.node.height / 2, m + 9, 0, Math.PI / 180 * 360, false);
        h.fill();
        for (var k = 0; k < n; k++) {
            if (k == 0) {
                h.fillStyle = c.options.nothing;
                h.strokeStyle = c.options.nothing
            } else {
                h.fillStyle = c.options.color[k % 2];
                h.strokeStyle = c.options.borderColor[k % 2]
            }
            h.beginPath();
            h.moveTo(c.canvas.node.width / 2, c.canvas.node.height / 2);
            h.arc(c.canvas.node.width / 2, c.canvas.node.height / 2, m, l, l + (Math.PI * 2 * (1 / n)), false);
            h.lineTo(c.canvas.node.width / 2, c.canvas.node.height / 2);
            h.fill();
            h.lineWidth = 3;
            l += Math.PI * 2 * (1 / n);
            c.awards[a[k]["id"]] = l / Math.PI * 180
        }
        l = 0;
        h.translate(c.canvas.node.width / 2, c.canvas.node.height / 2);
        var j = (Math.PI * 2 * (1 / n));
        for (var k = 0; k < n; k++) {
            if (k == 0) {
                h.fillStyle = "#FFFFFF"
            } else {
                h.fillStyle = c.options.fontColor[k % 2]
            }
            if (k == 0) {
                h.rotate(j / 2)
            } else {
                h.rotate(j)
            }
            h.font = "bold 17px sans-serif";
            h.textBaseline = "middle";
            h.textAlign = "center";
            h.fillText(a[k].name, 0 + m / 1.5, 0)
        }
    };
    g()
};

var wheel = {
    name: "wheel",
    defaults: {
        awards_info: "恭喜您中奖，请联系我们确认信息，我们的联系方式是",
        awards: [{
            id: 1,
            name: "一等奖",
            content: "imac",
            probability: 10000,
            num: 1
        },
        {
            id: 2,
            name: "二等奖",
            content: "ipad",
            probability: 1000,
            num: 2
        },
        {
            id: 3,
            name: "三等奖",
            content: "ipad mini",
            probability: 100,
            num: 3
        }],
        times: 2,
        birthday: 0,
        name: 0,
        sex: 0,
        email: 0,
        qq: 0,
        address: 0
    },
    view: {
        initialize: function() {
            this.firsttime = true;
            this.render();
            this.rotating = false;
        },
        events: {
            "click .start:not(.disabled)": "showForm",
            "click .btn-b-s": "hideResult"
        },
        hideResult: function() {
            this.$el.find(".result").hide()
        },
        showForm: function() {
            if (this.rotating) {
                return
            }
            
            this.price();return false;
            
            //第一次要求补全资料：手机号和姓名
            if (this.firsttime) {
                var a = this.$el.find(".contact");
                a.css({
                    width: $("#LQ").width() - 30 - 20,
                    display: ""
                });
                var b = this.$el.find(".result");
                b.css({
                    width: $("#LQ").width() - 30 - 40,
                    display: ""
                })
            } else {
                this.price()
            }
        },
        price: function() {
            var b = this.$el.find(".contact form"),
            f = b.find('input[name="phone"]').val();
            var g = this.$el.find(".result");
            var k = this;
            var c = b.serialize();
            
            if(wx_id == ''){
                alert("手机号码未绑定");
                return false;
            }
            
            if(all_points < minus_score){
                alert("积分余额不足");
                return false;
            }else{
                all_points -= minus_score;
                $('#all_points').html(all_points);
            }
            
            /*
            var h = /^1[3-8]\d{9}$/;
            if (!f || !h.test(f)) {
                if (f) {
                    alert("手机号格式不正确")
                }
                return
            }
            var a = b.find("input[placeholder]");
            for (var e = 0; e < a.length; e++) {
                var d = a.eq(e);
                if (!d.val()) {
                    alert(d.attr("placeholder") + "必填");
                    d.focus();
                    return
                }
            }
            */
            k.wheel.rotate();
            this.rotating = true;
            this.$el.find(".contact").hide();
            this.$el.find(".start").addClass("disabled");
            location.href = "#";
            $.ajax({
                //url: widgets.submit_url,
                url: 'BigWheel',
                type: 'POST',
                data: c + "&wx_id="+wx_id+'&lottery_id='+lottery_id,
                success: function(i) {
                    //alert(i);return false;
                    i = JSON.parse(i);
                    if (i) {
                        log_id = i.log_id;
                        k.wheel.stopRotate(i.prize_id,
                        function() {
                            k.rotating = false;
                            g.show();
                            g.find("li").hide();
                            g.find("#award_" + i.prize_id).show();
                            if (i.prize_id == 0) {
                                if (i.left == 0) {
                                    g.find("#award_zero").show()
                                } else {
                                    g.find("#award_more strong").text(i.left);
                                    g.find("#award_more").show()
                                }
                            }
                        })
                    } else {
                        k.wheel.stopRotate(i.prize_id);
                        setTimeout(function(){alert(i.prize);},1000);
                    }
                    k.$el.find(".start").removeClass("disabled")
                }
            })
        },
        render: function() {
            //var g = this.model.toJSON().data,
            var g = this.model.data,
            a = [],
            k = g.awards.length;
            g.id = "w_" + new Date();
            var j = this;
            a.push({
                id: 0,
                name: "谢谢参与"
            });
            for (var d = 0; d < k; d++) {
                a.push(g.awards[d])
            }
            //var h = this.template(g);
            //this.$el.addClass("wheel disabled noDel").html(h);
            var f = this.$el.find(".canvas-box")[0],
            e = this.$el.find(".contact"),
            c = this.$el.find(".user-list");
            e.find("form").on("submit",
            function(b) {
                b.preventDefault();
                j.firsttime = false;
                j.price()
            });
            this.wheel = new Wheel(f, a, {
                speed: 10
            });
            
            /*
            $.ajax({
                url: widgets.submit_url + "&act=prize_result",
                data: "case_id=" + widgets.case_id,
                success: function(b) {
                    b = JSON.parse(b);
                    if (b && b.status) {
                        $.each(b.prize_result,
                        function(i, l) {
                            $('<li class="user-item">' + l + "</li>").appendTo(c)
                        })
                    } else {
                        $('<li class="user-item">还没有用户中奖哦</li>').appendTo(c)
                    }
                }
            });
            */
            
            var accc = this;
            $('span.start:not(.disabled)').click(function(){accc.showForm();});
            
            return this
        }
    }
};