<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html class="no-js" lang="zh-CN">
<head>
	<title><?php if($info["title"] !=''): echo ($info["title"]); ?>-<?php endif; echo C('WEB_SITE_TITLE');?></title>
<meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0;" name="viewport" />
<meta name="format-detection" content="telephone=no" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">



<link href="/Public/Wechat/FontAwesome/css/font-awesome.css" rel="stylesheet" />
<link href="/Public/Wechat/bootstrap/css/bootstrap.css" rel="stylesheet" />
<link href="/Public/Wechat/css/style.css" rel="stylesheet" />


<script src="/Public/Wechat/js/jquery.js" type="text/javascript"></script>
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
	


<link rel="stylesheet" href="/Public/Wechat/mslide/css/idangerous.swiper.css">
<link rel="stylesheet" href="/Public/Wechat/mslide/css/swiper-demos.css?v=1.8">


<div class="header">
	<a href="/"><img src="/Public/Wechat/images/logo.jpg" /></a>
</div>
<style>
.index-slide .con
{ width:100%;  overflow:hidden;}
.index-slide .con ul
{ width:999em; height:100%; list-style:none;}
.index-slide .con ul li
{ width:100%; height:100%; float:left; position:relative;}
</style>

<div class="index-slide" <?php if(($slide_status) == "0"): ?>style="display:none;"<?php endif; ?> >
	<div class="con">
    	<ul>
        	<?php if(is_array($ads)): $i = 0; $__LIST__ = $ads;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><li class="slideli"><a href="<?php echo ($list["url"]); ?>"><img src="<?php echo (picture($list["picture"])); ?>"/></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</div>

<script type="text/javascript">
	$(function(){
		var slide_index = 0;
		var slide_count = $(".slideli").length;
		var w = $(".slideli").width();
		setInterval(function(){
			if(slide_index<slide_count-1){
				$(".slideli").animate({"left":"-="+w+"px"});
				slide_index = slide_index + 1;
			}else{
				$(".slideli").animate({"left":"0px"});
				slide_index = 0;
			}
			
		},2000);
	});
</script>


<div class="index-search">
	<form action="<?php echo U('Product/search');?>" method="get" class="search-form">
	<div class="left">
    	<input type="text" name="key" class="key" value="请输入您所需要的" style="color: #333;" />
    </div>
    <div class="right">
    	<input type="submit" value="搜索" />
    </div>
    </form>
</div>
	<div style=" clear:both;height:10px;">&nbsp;</div>
<script>
$(function(){
	$(".search-form").submit(function(){
		var key = $(".key").val();
		if(key=="请输入您所需要的" || key==""){
			$(".key").focus();
			return false;
		}
	});
	$(".key").focus(function(){
		if($(this).val()=="请输入您所需要的"){
			$(this).val("");
		}
	});
});
</script>

	<style>
		.seckill{
			clear: both;
			width: 94%;
			height: auto;
			margin: 0px auto;
		}
		.seckill .head{
			width: 100%;
			height: 50px;
			position: relative;
		}
		.seckill .head .bg{
			width: 100%;
			height: auto;
			z-index: 1;
			position: absolute;
			left:0px;
			top:0px;
		}
		.seckill .head .bg img{

		}
		.seckill .head .time{
			width: 40%;
			position: absolute;
			right: 0px;
			top:8px;
			z-index: 2;
		}
		.seckill .time div{
			float: left;
		}
		.seckill .time .dian{
			width: 4px;
			height: 18px;
			background: url(/Public/Wechat/images/msdian.png) no-repeat;
			margin: 5px 10px;
		}
		.seckill .time .ms{
			width: 26px;
			height: 29px;
			background: url("/Public/Wechat/images/mstime.png") no-repeat;
			color: #fff;
			line-height: 29px;
			text-align: center;
		}
	</style>
	<?php if($seckill == true): ?><style>
			.seckill ul li{
				width: 96%;
				position: relative;
			}
			.seckill ul li .right a{
				width: 80px;
				height: 35px;
				display: block;
				line-height: 35px;
				background: #ff4b90;
				color: #fff;
				border-radius: 6px;
				position: absolute;
				right:10px;
				bottom: 10px;
			}
		</style>
		<div class="seckill">
			<div class="list">
				<ul>
					<?php if(is_array($seclist)): $i = 0; $__LIST__ = $seclist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$seclist): $mod = ($i % 2 );++$i;?><li>
							<div class="img"><a href="<?php echo U('Product/detail?id='.$seclist['id']);?>"><img src="<?php echo (thumb($seclist["cover_id"],260,260)); ?>" class="img-responsive" /></a></div>
							<div class="info">
								<div class="title"><a href="<?php echo U('Product/detail?id='.$seclist['id']);?>"><?php echo ($seclist["title"]); ?></a></div>
								<div class="bottom">
									<div class="left">
										<p class="price" style="font-size: 16px;">秒杀价：￥<?php echo (auto_price($seclist["proid"])); ?></p>
									</div>
									<div class="right" style="">
										<a href="<?php echo U('Product/detail?id='.$seclist['id']);?>">去抢购</a>
									</div>
									<div class="time" style="clear: both; margin-top: 30px;">
										<div class="hour ms">00</div>
										<div class="dian"></div>
										<div class="fen ms">00</div>
										<div class="dian"></div>
										<div class="miao ms">00</div>
									</div>
								</div>
							</div>
						</li><?php endforeach; endif; else: echo "" ;endif; ?>
				</ul>
			</div>
			<script>
				$(function(){
					setInterval(function(){
						$.get("<?php echo U('server_time?end_time='.$seckill['end_time']);?>",function(time){
							$(".seckill .hour").html(time[0]);
							$(".seckill .fen").html(time[1]);
							$(".seckill .miao").html(time[2]);
						});
					},1000);
				});
			</script>
		</div><?php endif; ?>

	<div style=" clear:both;height:10px;">&nbsp;</div>
	<div style="width:94%; margin:0px auto;">
		<a href="<?php echo ($zhiding["url"]); ?>"><img src="<?php echo (picture($zhiding["picture"])); ?>"  <?php if(($zhiding_status) == "0"): ?>style="display:none;"<?php else: ?>style="width:100%;"<?php endif; ?> /></a>
	</div>
	<div style=" clear:both;height:10px;">&nbsp;</div>
	<div style="width:94%; margin:0px auto;">
		<a href="<?php echo ($mrthq["url"]); ?>"><img src="<?php echo (picture($mrthq["picture"])); ?>"  <?php if(($mrthq_status) == "0"): ?>style="display:none;"<?php else: ?>style="width:100%;"<?php endif; ?> /></a>
	</div>
<div style=" clear:both;height:10px;">&nbsp;</div>
<div style="width:94%; margin:0px auto;">
<a href="<?php echo ($gg["url"]); ?>"><img src="<?php echo (picture($gg["picture"])); ?>"  <?php if(($ggstatus) == "0"): ?>style="display:none;"<?php else: ?>style="width:100%;"<?php endif; ?> /></a>
</div>
<!--暂时关闭精品推荐
<div class="index-jp">
	<div class="head">
    	<div class="bg">精品推荐</div>
    </div>
    <div class="con">
    	<div class="swiper-container swiper-loop" style="margin:0px; height:auto;">
        <div class="pagination-loop"></div>
        <div class="swiper-wrapper">
          <?php if(is_array($jingcai)): $i = 0; $__LIST__ = $jingcai;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$jclist): $mod = ($i % 2 );++$i;?><div class="swiper-slide">
          	<img src="<?php echo (picture($jclist["cover_id"])); ?>" style=" height:90%; border:1px solid #ccc; border-radius:5px; vertical-align:top;" />
          </div><?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
      </div>
    </div>
</div>
-->


<div class="index-href" <?php if(($jp_status) == "0"): ?>style="display:none;"<?php endif; ?> >
	<a href="<?php echo ($jpads["url"]); ?>"><img src="<?php echo (picture($jpads["picture"])); ?>" /></a>
</div>

<div class="index-bk">
	<div class="head">
    	<div class="bg"><span class="title">当季推荐</span><span class="more"><a href="">MORE》</a></span></div>
    </div>
    <div class="con">
    	<div class="top" <?php if(($dj_status) == "0"): ?>style="display:none;"<?php endif; ?> ><a href="<?php echo ($djads["url"]); ?>"><img src="<?php echo (picture($djads["picture"])); ?>" /></a></div>
        <div class="box">
        	<ul>
            	<?php if(is_array($baokuan)): $i = 0; $__LIST__ = $baokuan;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><li>
                	<div class="img"><a href="<?php echo U('Product/detail?id='.$list['id']);?>"><img src="<?php echo (thumb($list["cover_id"],260,260)); ?>" class="img-responsive" /></a></div>
                    <div class="info">
                    	<div class="title"><a href="<?php echo U('Product/detail?id='.$list['id']);?>"><?php echo ($list["title"]); ?></a></div>
                        <div class="bottom">
                        	<div class="left">
                            	<p class="price">会员价 ￥<?php echo auto_price($list['id']);?></p>
                                <p class="scj">市场价 ￥<?php echo ($list["price"]); ?></p>
                            </div>
                            <div class="right">
                            	总销量<br /><span><?php echo ($list["xiaoliang"]); ?></span>
                            </div>
                        </div>
                    </div>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php if(is_array($catelist)): $i = 0; $__LIST__ = $catelist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><div class="index-list">
	<div class="head">
    	<div class="bg"><span class="title"><?php echo ($list["title"]); ?></span><span class="more"><a href="<?php echo U('Product/lists?category='.$list['name']);?>">MORE》</a></span></div>
    </div>
    <div class="con">
    	<?php if(($list["pid"]) != "306"): ?><div class="top"><a href="<?php echo ($list["url"]); ?>"><img src="<?php echo (picture($list["icon"])); ?>" /></a></div><?php endif; ?>
        <div class="box">
        	<ul>
            	<?php if(is_array($list["list"])): $i = 0; $__LIST__ = $list["list"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$prolist): $mod = ($i % 2 );++$i;?><li>
                	<div class="img"><a href="<?php echo U('Product/detail?id='.$prolist['id']);?>"><img src="<?php echo (thumb($prolist["cover_id"],260,260)); ?>" class="img-responsive" /></a></div>
                    <div class="info">
                    	<div class="title"><a href="<?php echo U('Product/detail?id='.$prolist['id']);?>"><?php echo ($prolist["title"]); ?></a></div>
                        <div class="bottom">
                        	<div class="left">
                            	<p class="price">会员价 ￥<?php echo auto_price($prolist['id']);?></p>
                                <p class="scj">市场价 ￥<?php echo ($prolist["price"]); ?></p>
                            </div>
                            <div class="right">
                            	总销量<br /><span><?php echo ($prolist["xiaoliang"]); ?></span>
                            </div>
                        </div>
                    </div>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
            </ul>
        </div>
    </div>
</div><?php endforeach; endif; else: echo "" ;endif; ?>
<script>
var wh = $(window).width();
if(wh>680){
	wh = 680;
}

$(".slideli img").css({"width":wh});
var h = $(".slideli img").height();
$(".index-slide .con").css({"height":h+"px"});
$(".index-slide .con ul").css({"height":h+"px"});
$(".slideli").css({"width":wh});
</script>
<!-- Swiper -->
<script  src="/Public/Wechat/mslide/js/idangerous.swiper-1.9.1.min-index.js"></script>
<!-- Swiper Scrollbar plugin -->
<script  src="/Public/Wechat/mslide/js/idangerous.swiper.scrollbar-1.2.js"></script>
<!-- Demos code -->
<script  src="/Public/Wechat/mslide/js/swiper-demos.js"></script>


    </div>
	<!-- /主体 -->

	
	<div style=" clear:both; margin-bottom:72px;"></div>
    <div class="footer">
        <a href="<?php echo U('index/index');?>"<?php if(($modelname) == "Indexindex"): ?>class='focus'<?php endif; ?> ><span class="icon-home"></span><br />首页</a>
        <a href="<?php echo U('product/index');?>" <?php if(($modelname) == "Productindex"): ?>class='focus'<?php endif; ?>><span class="icon-th-large"></span><br />产品分类</a>
        <a href="<?php echo U('Shopping/cart');?>" <?php if(($modelname) == "Shoppingcart"): ?>class='focus'<?php endif; ?>><span class="icon-shopping-cart"></span><br />购物车</a>
        <a href="<?php echo U('User/index');?>" <?php if(($modelname) == "Userindex"): ?>class='focus'<?php endif; ?>><span class="icon-user"></span><br />个人中心</a>
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