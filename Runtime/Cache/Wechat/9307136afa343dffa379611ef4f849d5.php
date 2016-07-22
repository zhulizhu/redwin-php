<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html class="no-js" lang="zh-CN">
<head>
	<title><?php if($info["title"] !=''): echo ($info["title"]); ?>-<?php endif; echo C('WEB_SITE_TITLE');?></title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="/Public/Wechat/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="/Public/Wechat/css/old_style.css">
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
	
<div class="top-head">
    <h2>用户注册</h2>
</div>
<link href="/Public/Wechat/iCheck/skins/flat/red.css" type="text/css" rel="stylesheet" />
<script src="/Public/Wechat/iCheck/icheck.min.js" type="text/javascript"></script>

<div class="reg">

	<div class="bg">
    	<h2><?php echo C('WEB_SITE_TITLE');?></h2>
        <p>立即加入我们，获得更多体验</p>
    </div>
    
    <div class="content">
    	<?php if(!empty($frominfo)): ?><div class="frominfo">
        	
            <div class="img"><img src="<?php echo ($frominfo["headimgurl"]); ?>"></div>
            <div class="title"><?php echo ($frominfo["username"]); ?> 邀请您加入！</div>
            
        </div><?php endif; ?>
        
        <div class="form">
        	<form action="<?php echo U('register');?>" method="post" class="regform">
            	<ul>
                	<li>
                    	<div class="left">手机号：</div>
                        <div class="right"><input type="tel" name="mobile" class="mobile" value="请输入手机号码"></div>
                    </li>
                    <li>
                    	<div class="left">验证码：</div>
                        <div class="right"><input type="text" name="verify" class="verify" value="请输入收到的验证码"></div>
                        <div class="ver"><a href="javascript:;" class="send_sms">发送验证码</a></div>
                    </li>
                </ul>
                <div style="text-align:center; margin-top:20px;">
                	<label><input type="checkbox" class="xieyi" checked="checked" value="1" />&nbsp;<a href="<?php echo U('Article/detail?id=992');?>">《<?php echo C('WEB_SITE_TITLE');?>用户服务协议》</a></label>
                </div>
                <div style=" text-align:center; margin-top:20px;">
            	<input type="submit" value="立即注册" class="submit">
                </div>
            </form>
        </div>
        
    </div>

</div>

<script type="text/javascript">

$(function(){
	$('.xieyi').iCheck({
    	checkboxClass: 'icheckbox_flat-red',
    	increaseArea: '20%' // optional
  	});
	$('.xieyi').on('ifChecked', function(event){
		$(".xieyi").val("1");
	});
	$('.xieyi').on('ifUnchecked', function(event){
		$(".xieyi").val("0");
	});
});
var height = $(document).height();
var topheight = $(".top-head").height();
var height = height-topheight-72;
$(".reg").css({"min-height":height});

$(".send_sms").click(function(){
	var mobile = $(".mobile").val();
	if(mobile.length!=11){
		alert("请输入11位手机号码");
	}else{
		var url = "<?php echo U('reg_send_sms');?>?mobile="+mobile;
		$.get(url,function(data){
			if(data==true){
				$(".ver").addClass("focus");
				$(".send_sms").html("60秒后重新获取");
				var timer = 60;
				var djs = setInterval(function(){
					if(timer>0){
						timer = timer - 1;
						var str = timer+"秒后重新获取";
						$(".send_sms").html(str);
					}else{
						clearInterval(djs);
						$(".ver").removeClass("focus");
						$(".send_sms").html("发送验证码");
					}
				},1000)
			}
		});
	}
	
	return false;
});
$(".regform").submit(function(){
	
	var status = $(".xieyi").val();
	if(status==0){
		alert("您还未同意用户服务协议");
		return false;
	}
	
	var mobile = $(".mobile").val();
	if(mobile.length!=11){
		alert("请输入11位手机号码");
		return false;
	}
	var verify = $(".verify").val();
	if(verify.length!=4){
		alert("请输入4位验证码");
		return false;
	}
	
});
$(".form ul input").focus(function(){
	$(this).val("");
	$(this).addClass("focus");
});
</script>

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
                <a href="<?php echo U('User/index');?>">
                    <li id="mymy" class="unbindClick" value=1>
                        <img src="/Public/Wechat/images/hfhfh_17.png" id="img_d" alt="">
                        个人中心
                    </li>
                </a>
            </ul>
        </div>
    
    
	<!-- 底部 -->
	

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