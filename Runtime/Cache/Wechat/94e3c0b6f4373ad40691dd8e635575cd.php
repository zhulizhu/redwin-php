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
	
<div id="myself">
    <div class="head"><a href="myinfo.html"><img  class="set" src="/Public/Wechat/images/icon-options.png"></a></div>
    <div class="info">
        <div class="one">
            <div class="tu">
                <img src="/Public/Wechat/images/bg_02.png"/>
            </div>
            <div class="mage">
                <span><a href="" style="margin-left: 6px">会笑的哈哈<img src="/Public/Wechat/images/gou.png"></a></span>
                <span class="user">您当前为尊敬vip用户</span>
            </div>
					<span class="bg-sp" style="position: absolute;right:1%;z-index:101">
					D级</span>
        </div>
        <div class="two">
            <div style="width: 27px;height: 23px;float: left;"><img src="/Public/Wechat/images/1.png" style="width: 27px;height: 23px;"/></div>
            <span><a href="">四川省成都市武侯区</a></span>
            <span  class="sex"> <a href="">男</a></span>
            <img class="open" src="/Public/Wechat/images/16.png"/>
        </div>
        <ul>
            <li>
                <div>
                    <img src="/Public/Wechat/images/2.png" style="width:30px;height:30px;">
                </div>
                <div class="">
                    <p>个人业绩</p>

                    <p>
                        <a href="">102</a>
                    </p>
                </div>
            </li>
            <li>
                <div>
                    <img src="/Public/Wechat/images/tuanduiyeji.png" alt="" style="width:33px;height:25px;">
                </div>
                <div class="">
                    <p>团队业绩</p>

                    <p>
                        <a href="">102</a>
                    </p>
                </div>
            </li>
            <li>
                <div>
                    <img src="/Public/Wechat/images/qianbao-.png" style="width:26px;height:30px;">
                </div>
                <div class="">
                    <p>可用余额</p>

                    <p>
                        <a href="">￥102</a>
                    </p>
                </div>
            </li>

        </ul>
    </div>
    <!--info over-->
    <div class="dingdan">
        <div class="tit">
            <a href="all_order.html">
                <div><img src="/Public/Wechat/images/22.png" /></div>
                <span>我的订单</span>
                <span class="look">查看全部订单
						<span class="next">›</span>
					</span>
            </a>
        </div>
        <ul>
            <li>
                <a href="paid.html">
                    <div>
                        <img src="/Public/Wechat/images/7.png"/>
                    </div>

                    <span>待支付</span>

                </a>
            </li>
            <li>
                <a href="product_pay_off.html">
                    <div>
                        <img src="/Public/Wechat/images/diew.png"/>
                    </div>

                    <span>已支付</span>

                </a>
            </li>
            <li>
                <a href="product.html">
                    <div>
                        <img src="/Public/Wechat/images/9.png"/>
                    </div>

                    <span>待评价</span>
                </a>
            </li>
            <li>
                <a href="sales_return.html">
                    <div>
                        <img src="/Public/Wechat/images/10.png"/>
                    </div>

                    <span>退货</span>
                </a>
            </li>

        </ul>
    </div>
    <!--我的订单over-->
    <div class="my_list">
        <a href="mymoney.html">
            <div class="list">
                <div>
                    <img src="/Public/Wechat/images/yrdswhjvcg.png"/>
                </div>
					<span>
						我的钱包
					</span>
					<span>
						&rsaquo;
					</span>

            </div>
        </a>
        <a href="myteam.html">
            <div class="list">
                <div>
                    <img src="/Public/Wechat/images/hdsd.png"/>
                </div>
					<span>
						我的团队
					</span>
					<span>
						&rsaquo;
					</span>

            </div>
        </a>
        <a href="discount.html">
            <div class="list">
                <div>
                    <img src="/Public/Wechat/images/hstetet.png"/>
                </div>
					<span>
						我的优惠券
					</span>
					<span>
						&rsaquo;
					</span>
            </div>
        </a>
        <a href="addres_defaul.html">
            <div class="list">
                <div>
                    <img src="/Public/Wechat/images/werer.png"/>
                </div>
					<span>
						我的收货地址
					</span>
					<span>
						&rsaquo;
					</span>
            </div>
        </a>
        <a href="addres_defaul.html">
            <div class="list">
                <div>
                    <img src="/Public/Wechat/images/money.png"/>
                </div>
					<span>
						我的红包
					</span>
					<span>
						&rsaquo;
					</span>
            </div>
        </a>
        <!--list over-->
    </div>
    <!--mylist over-->
</div>
<!--bottom nav bar-->
<div style="height:150px;"></div>
<div class="fot">
    <ul class="fot_ul">
        <a href="../Product/index.html">
            <li class="unbindClick" id="home" value=1>
                <img src="/Public/Wechat/images/hdshsdh_03.png" id="img_a" alt="">
                首页
            </li>
        </a>
        <a href="../Product/sort.html">
            <li id="feng" class="unbindClick" value=1>
                <img src="/Public/Wechat/images/khkh_03.png" id="img_b" alt="">
                分类
            </li>
        </a><a href="../Product/buy_cart.html">
        <li id="cart" class="unbindClick" value=1>
            <img src="/Public/Wechat/images/hfhfh_15.png" id="img_c" alt="">
            购物车
        </li>
    </a>
        <a href="javascript:void(0)">
            <li id="mymy" class=" public_fot unbindClick" value=1>
                <img src="/Public/Wechat/images/gerenzhongxin.png" id="img_d" alt="">
                个人中心
            </li>
        </a>

    </ul>
</div>

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