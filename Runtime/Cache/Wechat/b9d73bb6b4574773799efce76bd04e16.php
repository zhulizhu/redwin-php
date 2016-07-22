<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html class="no-js" lang="zh-CN">
<head>
	<title><?php if($info["title"] !=''): echo ($info["title"]); ?>-<?php endif; echo C('WEB_SITE_TITLE');?></title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="/Public/Wechat/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/Public/Wechat/css/style.css">
<script src="/Public/Wechat/js/jquery-1.10.2.min.js"></script>
<script src="/Public/Wechat/js/bootstrap.min.js"></script>


<script src="/Public/Wechat/js/jquery.pjax.js" type="text/javascript"></script>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>

<script type="text/javascript">
wx.config({
    debug: false,
    appId: '<?php echo ($signPackage["appId"]); ?>',
    timestamp: <?php echo ($signPackage["timestamp"]); ?>,
    nonceStr: '<?php echo ($signPackage["nonceStr"]); ?>',
    signature: '<?php echo ($signPackage["signature"]); ?>',
	jsApiList: [
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
	]
});
wx.ready(function () {
	wx.onMenuShareAppMessage({
		title: '<?php echo ($sharetitle); echo C(WEB_SITE_TITLE);?>', // 分享标题
		desc: '<?php echo C(WEB_SITE_DESCRIPTION);?>', // 分享描述
		link: '<?php echo ($shareurl); ?>?openid=<?php echo ($openid); ?>', // 分享链接
		imgUrl: '<?php if(empty($shareimg)): ?>http://<?php echo C(WEB_SITE_DOMAIN);?>/public/wechat/images/getheadimg.jpg<?php else: echo ($shareimg); endif; ?>', // 分享图标
		type: '', // 分享类型,music、video或link，不填默认为link
		dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
		success: function () { 
			
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	});
	wx.onMenuShareTimeline({
		title: '<?php echo ($sharetitle); echo C(WEB_SITE_TITLE);?>', // 分享标题
		link: '<?php echo ($shareurl); ?>?openid=<?php echo ($openid); ?>', // 分享链接
		imgUrl: '<?php if(empty($shareimg)): ?>http://<?php echo C(WEB_SITE_DOMAIN);?>/public/wechat/images/getheadimg.jpg<?php else: echo ($shareimg); endif; ?>', // 分享图标
		success: function () { 
			
		},
		cancel: function () { 
		}
	});
	wx.onMenuShareQQ({
		title: '<?php echo ($sharetitle); echo C(WEB_SITE_TITLE);?>', // 分享标题
		desc: '<?php echo C(WEB_SITE_DESCRIPTION);?>', // 分享描述
		link: '<?php echo ($shareurl); ?>?openid=<?php echo ($openid); ?>', // 分享链接
		imgUrl: '<?php if(empty($shareimg)): ?>http://<?php echo C(WEB_SITE_DOMAIN);?>/public/wechat/images/getheadimg.jpg<?php else: echo ($shareimg); endif; ?>', // 分享图标
		success: function () { 
		   // 用户确认分享后执行的回调函数
		},
		cancel: function () { 
		   // 用户取消分享后执行的回调函数
		}
	});
	wx.onMenuShareWeibo({
		title: '<?php echo ($sharetitle); echo C(WEB_SITE_TITLE);?>', // 分享标题
		desc: '<?php echo C(WEB_SITE_DESCRIPTION);?>', // 分享描述
		link: '<?php echo ($shareurl); ?>?openid=<?php echo ($openid); ?>', // 分享链接
		imgUrl: '<?php if(empty($shareimg)): ?>http://<?php echo C(WEB_SITE_DOMAIN);?>/public/wechat/images/getheadimg.jpg<?php else: echo ($shareimg); endif; ?>', // 分享图标
		success: function () { 
		   // 用户确认分享后执行的回调函数
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	});
	wx.onMenuShareQZone({
		title: '<?php echo ($sharetitle); echo C(WEB_SITE_TITLE);?>', // 分享标题
		desc: '<?php echo C(WEB_SITE_DESCRIPTION);?>', // 分享描述
		link: '<?php echo ($shareurl); ?>?openid=<?php echo ($openid); ?>', // 分享链接
		imgUrl: '<?php if(empty($shareimg)): ?>http://<?php echo C(WEB_SITE_DOMAIN);?>/public/wechat/images/getheadimg.jpg<?php else: echo ($shareimg); endif; ?>', // 分享图标
		success: function () { 
		   // 用户确认分享后执行的回调函数
		},
		cancel: function () { 
			// 用户取消分享后执行的回调函数
		}
	});
});
</script>
<!-- 页面header钩子，一般用于加载插件CSS文件和代码 -->
<?php echo hook('pageHeader');?>
</head>
<body>

	<!-- 头部 -->
	
    
    
	<!-- /头部 -->
	
	<!-- 主体 -->
    <div id="main" >
	
<style>
section
{ width:90%; height:auto; margin:0px auto;}
.control-group
{ margin:5px 0px;}
.span3
{ width:200px; height:40px; line-height:40px; text-indent:10px; border-radius:6px;}
</style>
<div class="top-head">
	<h2>用户登录</h2>
</div>

<section>
	<div class="span12">
        <form class="login-form" action="/User/login.html" method="post">
          <div class="control-group">
            <label class="control-label" for="inputEmail">用户名</label>
            <div class="controls">
              <input type="text" id="inputEmail" class="span3" placeholder="请输入用户名"  ajaxurl="/member/checkUserNameUnique.html" errormsg="请填写1-16位用户名" nullmsg="请填写用户名" datatype="*1-16" value="" name="username">
            </div>
          </div>
          <div class="control-group">
            <label class="control-label" for="inputPassword">密码</label>
            <div class="controls">
              <input type="password" id="inputPassword"  class="span3" placeholder="请输入密码"  errormsg="密码为6-20位" nullmsg="请填写密码" datatype="*6-20" name="password">
            </div>
          </div>
          
          <?php if(C('USER_LOGIN_VERIFY')==1){ ?>
          <div class="control-group">
            <label class="control-label" for="inputPassword">验证码</label>
            <div class="controls">
              <input type="text" id="inputPassword" class="span3" placeholder="请输入验证码"  errormsg="请填写5位验证码" nullmsg="请填写验证码" datatype="*5-5" name="verify">
            </div>
          </div>
          
          <div class="control-group">
            <label class="control-label"></label>
            <div class="controls">
                <img class="verifyimg reloadverify" alt="点击切换" src="<?php echo U('verify');?>" style="cursor:pointer;">
            </div>
            <div class="controls Validform_checktip text-warning"></div>
          </div>
          <?php } ?>
          <div class="control-group">
            <div class="controls">
              <label class="checkbox">
                <input type="checkbox"> 自动登陆
              </label>
              <button type="submit" class="btn">登 陆</button>
            </div>
          </div>
        </form>
	</div>
    <div>
    	普通用户：pta 一级代理：yja 二级代理：eja 三级代理：sja 营销总监：yxa 分公司总经理：fgsa<br />
        <strong>后续将转为自动登录！</strong>
    </div>
</section>

    </div>
	<!-- /主体 -->

	
        <div style="height:150px;"></div>
        <div class="fot">
            <ul class="fot_ul">
                <a href="<?php echo U('Index/index');?>">
                    <li class=" unbindClick" id="home" value=1>
                        <img src="/Public/Wechat/images/SHOUYE.png" id="img_a" alt="">
                        首页
                    </li>
                </a>
                <a href="<?php echo U('Product/index');?>">
                    <li id="feng" class="public_fot unbindClick" value=1>
                        <img src="/Public/Wechat/images/khkh_03.png" id="img_b" alt="">
                        分类
                    </li>
                </a><a href="<?php echo U('Shopping/cart');?>">
                <li id="cart" class="unbindClick" value=1>
                    <img src="/Public/Wechat/images/hfhfh_15.png" id="img_c" alt="">
                    购物车
                </li>
            </a>
                <a href="../User/myself.html">
                    <li id="mymy" class="unbindClick" value=1>
                        <img src="/Public/Wechat/images/hfhfh_17.png" id="img_d" alt="">
                        个人中心
                    </li>
                </a>
            </ul>
        </div>
    
    
	<!-- 底部 -->
	


	<script type="text/javascript">

    	$(document)
	    	.ajaxStart(function(){
	    		$("button:submit").addClass("log-in").attr("disabled", true);
	    	})
	    	.ajaxStop(function(){
	    		$("button:submit").removeClass("log-in").attr("disabled", false);
	    	});


    	$("form").submit(function(){
    		var self = $(this);
    		self.submit();
    		return false;

    		function success(data){
    			if(data.status){
    				window.location.href = data.url;
    			} else {
    				self.find(".Validform_checktip").text(data.info);
    				//刷新验证码
    				$(".reloadverify").click();
    			}
    		}
    	});

		$(function(){
			var verifyimg = $(".verifyimg").attr("src");
            $(".reloadverify").click(function(){
                if( verifyimg.indexOf('?')>0){
                    $(".verifyimg").attr("src", verifyimg+'&random='+Math.random());
                }else{
                    $(".verifyimg").attr("src", verifyimg.replace(/\?.*$/,'')+'?'+Math.random());
                }
            });
		});
	</script>
 <!-- 用于加载js代码 -->
<!-- 页面footer钩子，一般用于加载插件JS文件和JS代码 -->
<?php echo hook('pageFooter', 'widget');?>
<div class="hidden"><!-- 用于加载统计代码等隐藏元素 -->

</div>
<script type="text/javascript">



</script>
	<!-- /底部 -->

</body>
</html>