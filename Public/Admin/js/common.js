(function($){$.fn.hoverNow=function(option){var s=$.extend({style:"hover",over:"",out:""},option||{});$(this).hover(function(){var a=$(this);eval("a.addClass(s.style)"+s.over)},function(){var a=$(this);eval("a.removeClass(s.style)"+s.out)});return $(this)};
	$.fn.hoverDelay=function(option){
		var s=$.extend({style:"hover",over:"",out:"",delay:200},option||{});
		$(this).each(function(){
			var a=$(this),setT1,setT2;$(this).hover(function(){clearTimeout(setT2);setT1=setTimeout(function(){a.siblings().removeClass(s.style);eval("a.addClass(s.style)"+s.over)},s.delay)},function(){var a=$(this);clearTimeout(setT1);setT2=setTimeout(function(){eval("a.removeClass(s.style)"+s.out)},s.delay)})
		});return $(this)
	}
})(jQuery);
//dom加载完成后执行的js
;$(function(){

	//全选的实现
	$(".check-all").click(function(){
		$(".ids").prop("checked", this.checked);
	});
	$(".ids").click(function(){
		var option = $(".ids");
		option.each(function(i){
			if(!this.checked){
				$(".check-all").prop("checked", false);
				return false;
			}else{
				$(".check-all").prop("checked", true);
			}
		});
		$(this).siblings().click();
	});
	
	//异步调用，弹出提示
	$(".async-get").click(function(){
		var target;
		var that = this;
		if($(this).hasClass('confirm')){
			if(!confirm('确认要执行该操作吗?')){
                return false;
            }
		}
		if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
			var info='';
			if($(this).attr("title")){
				info = $(this).attr("title");
			}
			$.ajax({
				url:target,
				type:"GET",
				async:true,
				beforeSend: function(data){
					updateAlert(info,'alert-success');
					setTimeout(function(){
                    		location.reload();
                    	},1500);
				}
			});
		}
		return false;
	});
	
    $(".change-get").change(function(){
		var target;
		var that = this;
		if($(this).hasClass('confirm')){
			if(!confirm('确认要执行该操作吗?')){
                return false;
            }
		}
		var async = false;
		if($(this).hasClass('asynctrue')){
			async = true;
		}
		var targetform = $(this).attr('target-form');
		if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
			if(targetform!=""){
				target = target + "/" + targetform + "/" + $(this).val() + ".html";
			}
			$.ajax({
				url:target,
				type:"GET",
				async:async,
				success: function(data){
					if (data.status==1) {
                    	if (data.url) {
                        	updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
                    	}else{
                        	updateAlert(data.info,'alert-success');
                    	}
                    	setTimeout(function(){
                        	if (data.url) {
                            	location.href=data.url;
                        	}else if( $(that).hasClass('no-refresh')){
                            	$('#top-alert').find('button').click();
                        	}else{
                            	location.reload();
                        	}
                    	},1500);
                	}else{
                    	updateAlert(data.info);
                    	setTimeout(function(){
                        	if (data.url) {
                            	location.href=data.url;
                        	}else{
                            	$('#top-alert').find('button').click();
                        	}
                    	},1500);
                	}
				}
			});
		}	
	});

    //ajax get请求
    $('.ajax-get').click(function(){
        var target;
        var that = this;
        if ( $(this).hasClass('confirm') ) {
            if(!confirm('确认要执行该操作吗?')){
                return false;
            }
        }
        if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
            $.get(target).success(function(data){
                if (data.status==1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
                    }else{
                        updateAlert(data.info,'alert-success');
                    }
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else if( $(that).hasClass('no-refresh')){
                            $('#top-alert').find('button').click();
                        }else{
                            location.reload();
                        }
                    },1500);
                }else{
                    updateAlert(data.info);
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else{
                            $('#top-alert').find('button').click();
                        }
                    },1500);
                }
            });

        }
        return false;
    });
	

    //ajax post submit请求
    $('.ajax-post').click(function(){
        var target,query,form;
        var target_form = $(this).attr('target-form');
        var that = this;
        var nead_confirm=false;
        if( ($(this).attr('type')=='submit') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){
            form = $('.'+target_form);
            if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能
            	form = $('.hide-data');
            	query = form.serialize();
            }else if (form.get(0)==undefined){
            	return false;
            }else if ( form.get(0).nodeName=='FORM' ){
                if ( $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                if($(this).attr('url') !== undefined){
                	target = $(this).attr('url');
                }else{
                	target = form.get(0).action;
                }
                query = form.serialize();
            }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
                form.each(function(k,v){
                    if(v.type=='checkbox' && v.checked==true){
                        nead_confirm = true;
                    }
                })
                if ( nead_confirm && $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                query = form.serialize();
            }else{
                if ( $(this).hasClass('confirm') ) {
                    if(!confirm('确认要执行该操作吗?')){
                        return false;
                    }
                }
                query = form.find('input,select,textarea').serialize();
            }
            $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
            $.post(target,query).success(function(data){
                if (data.status==1) {
                    if (data.url) {
                        updateAlert(data.info + ' 页面即将自动跳转~','alert-success');
                    }else{
                        updateAlert(data.info ,'alert-success');
                    }
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else if( $(that).hasClass('no-refresh')){
                            $('#top-alert').find('button').click();
                            $(that).removeClass('disabled').prop('disabled',false);
                        }else{
                            location.reload();
                        }
                    },1500);
                }else{
                    updateAlert(data.info);
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else{
                            $('#top-alert').find('button').click();
                            $(that).removeClass('disabled').prop('disabled',false);
                        }
                    },1500);
                }
            });
        }
        return false;
    });

	/**顶部警告栏*/
	var content = $('#main');
	var top_alert = $('#top-alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});

    window.updateAlert = function (text,c) {
		text = text||'default';
		c = c||false;
		if ( text!='default' ) {
            top_alert.find('.alert-content').text(text);
			if (top_alert.hasClass('block')) {
			} else {
				top_alert.addClass('block').slideDown(200);
				// content.animate({paddingTop:'+=55'},200);
			}
		} else {
			if (top_alert.hasClass('block')) {
				top_alert.removeClass('block').slideUp(200);
				// content.animate({paddingTop:'-=55'},200);
			}
		}
		if ( c!=false ) {
            top_alert.removeClass('alert-error alert-warn alert-info alert-success').addClass(c);
		}
	};

    //按钮组
    (function(){
        //按钮组(鼠标悬浮显示)
        $(".btn-group").mouseenter(function(){
            var userMenu = $(this).children(".dropdown ");
            var icon = $(this).find(".btn i");
            icon.addClass("btn-arrowup").removeClass("btn-arrowdown");
            userMenu.show();
            clearTimeout(userMenu.data("timeout"));
        }).mouseleave(function(){
            var userMenu = $(this).children(".dropdown");
            var icon = $(this).find(".btn i");
            icon.removeClass("btn-arrowup").addClass("btn-arrowdown");
            userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
            userMenu.data("timeout", setTimeout(function(){userMenu.hide()}, 100));
        });

        //按钮组(鼠标点击显示)
        // $(".btn-group-click .btn").click(function(){
        //     var userMenu = $(this).next(".dropdown ");
        //     var icon = $(this).find("i");
        //     icon.toggleClass("btn-arrowup");
        //     userMenu.toggleClass("block");
        // });
        $(".btn-group-click .btn").click(function(e){
            if ($(this).next(".dropdown").is(":hidden")) {
                $(this).next(".dropdown").show();
                $(this).find("i").addClass("btn-arrowup");
                e.stopPropagation();
            }else{
                $(this).find("i").removeClass("btn-arrowup");
            }
        })
        $(".dropdown").click(function(e) {
            e.stopPropagation();
        });
        $(document).click(function() {
            $(".dropdown").hide();
            $(".btn-group-click .btn").find("i").removeClass("btn-arrowup");
        });
    })();

    // 独立域表单获取焦点样式
    $(".text").focus(function(){
        $(this).addClass("focus");
    }).blur(function(){
        $(this).removeClass('focus');
    });
    $("textarea").focus(function(){
        $(this).closest(".textarea").addClass("focus");
    }).blur(function(){
        $(this).closest(".textarea").removeClass("focus");
    });
});

/* 上传图片预览弹出层 */
function tanchu(obj){
	var winW = $(window).width();
    var winH = $(window).height();
	var src = obj.src;
	if(src === undefined){
		return false;
	}
	// 创建弹出框以及获取弹出图片
    var imgPopup = "<div id=\"uploadPop\" class=\"upload-img-popup\"></div>"
	//如果弹出层存在，则不能再弹出
    var popupLen = $(".upload-img-popup").length;
    if( popupLen < 1 ) {
    	$(imgPopup).appendTo("body");
        $(".upload-img-popup").html(
        	"<img src='"+ src +"' >" + "<a class=\"close-pop\" href=\"javascript:;\" title=\"关闭\"></a>"
        );
    }
	// 弹出层定位
    var uploadImg = $("#uploadPop").find("img");
    var popW = uploadImg.width();
    var popH = uploadImg.height();
    var left = (winW -popW)/2;
    var top = (winH - popH)/2 + 50;
	if(popW>winW){
		left = 50;
		uploadImg.css({
			"max-width":winW * 0.8,
		});
	}
	if(popH>winH){
		top = 80;
		uploadImg.css({
			"max-height":winH * 0.8,
		});
	}
    $(".upload-img-popup").css({
        "left": left,
        "top": top,
    });
}

function delpic(obj){
	
}

/* 上传图片预览弹出层 */
$(function(){
    $(window).resize(function(){
        var winW = $(window).width();
        var winH = $(window).height();
        $(".upload-pre-item img").click(function(){
        	//如果没有图片则不显示
        	if($(this).attr('src') === undefined){
        		return false;
        	}
            // 创建弹出框以及获取弹出图片
            var imgPopup = "<div id=\"uploadPop\" class=\"upload-img-popup\"></div>"
            var imgItem = $(this).parent().find(".upload-pre-item").html();

            //如果弹出层存在，则不能再弹出
            var popupLen = $(".upload-img-popup").length;
            if( popupLen < 1 ) {
                $(imgPopup).appendTo("body");
                $(".upload-img-popup").html(
                    imgItem + "<a class=\"close-pop\" href=\"javascript:;\" title=\"关闭\"></a>"
                );
            }

            // 弹出层定位
            var uploadImg = $("#uploadPop").find("img");
            var popW = uploadImg.width();
            var popH = uploadImg.height();
            var left = (winW -popW)/2;
            var top = (winH - popH)/2 + 50;
            $(".upload-img-popup").css({
                "max-width" : winW * 0.9,
                "left": left,
                "top": top
            });
        });

        // 关闭弹出层
        $("body").on("click", "#uploadPop .close-pop", function(){
            $(this).parent().parent().remove();
        });
    }).resize();

    // 缩放图片
    function resizeImg(node,isSmall){
        if(!isSmall){
            $(node).height($(node).height()*1.2);
        } else {
            $(node).height($(node).height()*0.8);
        }
    }
})

//标签页切换(无下一步)
function showTab() {
    $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();
}

//标签页切换(有下一步)
function nextTab() {
     $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
        showBtn();
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();

    $("#submit-next").click(function(){
        $(".tab-nav li.current").next().click();
        showBtn();
    });
}

// 下一步按钮切换
function showBtn() {
    var lastTabItem = $(".tab-nav li:last");
    if( lastTabItem.hasClass("current") ) {
        $("#submit").removeClass("hidden");
        $("#submit-next").addClass("hidden");
    } else {
        $("#submit").addClass("hidden");
        $("#submit-next").removeClass("hidden");
    }
}

//导航高亮
function highlight_subnav(url){
    $('.side-sub-menu').find('a[href="'+url+'"]').closest('li').addClass('current');
}
/*提交表单*/
function submitForm(){
 	document.getElementById("myform").submit();
}