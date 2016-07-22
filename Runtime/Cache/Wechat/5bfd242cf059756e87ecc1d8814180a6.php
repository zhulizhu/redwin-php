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
	
<div id="home_page">
    <header>
        <img src="/Public/Wechat/images/tyt_03.png" alt="">

        <div class="group">
            <form action="<?php echo U('Product/search');?>" method="get" class="index-search">
            <input type="text" class="form-control text-search" name="txt" placeholder="请输入商品名称">
            <a href="javascript:;" class="submit-search"> <img src="/Public/Wechat/images/tyt_06.png" alt=""></a>
            </form>
            <script>
                $(function(){
                    $(".submit-search").click(function(){
                        if($(".text-search").val()!=""){
                            $(".index-search").submit();
                        }
                    });
                });
            </script>
        </div>
    </header>
    <div class="head">
        <ul>
            <a href="category.html?id=one" target="_blank">
                <li><img src="/Public/Wechat/images/guojia.png" alt="">
                    <span>国家</span></li>
            </a>
            <a href="category.html?id=nine" target="_blank">
                <li><img src="/Public/Wechat/images/jiuju.png" alt="">
                    <span>颜色</span></li>
            </a>
            <a href="category.html?id=eight" target="_blank">
                <li><img src="/Public/Wechat/images/zhonglei.png" alt="">
                    <span>葡萄种类</span></li>
            </a>
            <a href="category.html?id=twelve" target="_blank">
                <li><img src="/Public/Wechat/images/jiuju.png" alt="">
                    <span>香槟·起泡</span></li>
            </a>

            <a href="category.html?id=thirteen" target="_blank">
                <li><img src="/Public/Wechat/images/jiaqiang1.png" alt="">
                    <span>加强型</span></li>
            </a>
            <a href="category.html?id=ten" target="_blank">
                <li><img src="/Public/Wechat/images/kougan.png" alt="">
                    <span>口感</span></li>
            </a>
            <a href="sort.html">
                <li><img src="/Public/Wechat/images/tehui.png" alt="">
                    <span>特惠专区</span></li>
            </a>
            <a href="category.html?id=elev" target="_blank">
                <li><img src="/Public/Wechat/images/jiuju-08.png" alt="">
                    <span>酒具</span></li>
            </a>
        </ul>
    </div>
    <!--轮播-->
    <div id="myCarousel" class="carousel slide con">
        <ol class="carousel-indicators">
            <?php if(is_array($prolist)): $i = 0; $__LIST__ = $prolist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$prokey): $mod = ($i % 2 );++$i;?><li data-target="#myCarousel" data-slide-to="<?php echo ($key); ?>"  <?php if(($key) == "0"): ?>class="active"<?php endif; ?> ></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ol>
        <div class="carousel-inner">
            <?php if(is_array($prolist)): $i = 0; $__LIST__ = $prolist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="banner-box item <?php if(($key) == "0"): ?>active<?php endif; ?> " onclick="location.href='<?php echo U('Product/detail?id='.$list['id']);?>'" style="width: 100%;height: 100%;overflow: hidden;">
                <div class="lef">
                    <a href="product_detail.html"><img src="<?php echo (picture($list["cover_id"])); ?>" alt=""></a>
                    <div class="dat">
                        <div class="tim"><?php echo (time_format($list["update_time"],'Y-m')); ?></div>
                        <div class="times"><?php echo (time_format($list["update_time"],'d')); ?></div>
                    </div>
                </div>
                <div class="rig">
                    <h4><?php echo ($list["title"]); ?></h4>
                    <p style="color:black !important;">地区：<a href="javascript:;"><?php echo (get_linkage_value($list["country"])); ?></a></p>
                    <p>种类：<a href="javascript:;"><?php echo (get_linkage_value($list["species"])); ?></a></p>
                    <p>色泽：<a href="javascript:;"><?php echo (get_linkage_value($list["color"])); ?></a></p>
                    <p>年份：<a href="javascript:;"><?php echo ($list["year"]); ?></a></p>
                    <p>酒庄：<a href="javascript:;"><?php echo (get_linkage_value($list["manor"])); ?></a></p>
                    <p>酒精度：<a href="javascript:;"><?php echo ($list["jiujingdu"]); ?></a></p>
                    <button><a href="<?php echo U('Product/detail?id='.$list['id']);?>">更多</a></button>
                </div>
            </div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
    </div>

    <div class="tt">
        <div>
            <span><button><a href="../Article/little_tips.html">小贴士</a></button></span>
            <span style="display: none;"><img onclick="collect()" id="colle" src="/Public/Wechat/images/dhfdhfh_48.png" alt="">22</span>
        </div>
        <div class="content" style="padding: 0px 20px;">
            <?php echo ($content); ?>
        </div>
    </div>

</div>
<script type="text/javascript">
    //轮播
    $("#myCarousel").carousel({
        interval: 5000
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