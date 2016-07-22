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
	<h2>购物车</h2>
    <div class="cart_edit"><a href="javascript:;" class="editcart">编辑</a></div>
    <div class="cart_selall"><label class="selalllabel"><input type="checkbox" name="status" class="allstatus" value="<?php echo ($data["id"]); ?>" checked="checked" />&nbsp;全选</label></div>
</div>
<link href="/Public/Wechat/iCheck/skins/flat/red.css" type="text/css" rel="stylesheet" />
<script src="/Public/Wechat/iCheck/icheck.min.js" type="text/javascript"></script>
<style>
.style_num2 a
{ width:24px; height:19px; display:block; border:1px solid #d9d9d9; float:left; text-align:center; line-height:19px; text-decoration:none; color:#b7b9ba;}
.style_num2 .numtxt
{ width:30px; height:19px; float:left; border:1px solid #d9d9d9; border-radius:0px; box-shadow:none; border-left:none; border-right:none; text-align:center;}
.style_num2 .qd
{ font-size:12px; width:50px; margin-left:5px; display:none;}
</style>
<form action="<?php echo U('cart?tpl=1');?>" method="post" class="cartform" >
<div class="cart" style="padding-bottom:70px;">
	<div class="lists">
    	<ul>
        	<?php if(is_array($NewsList)): $i = 0; $__LIST__ = $NewsList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?><li>
            	<div class="top">
                	<div class="btn" title="on"><input type="checkbox" name="status[]" class="status" value="<?php echo ($data["id"]); ?>" checked  /></div>
                    <div class="right">
                    	<div class="img"><a href="javascript:;" ><img src="<?php echo (picture($data["cover_id"])); ?>" class="img-responsive" width="60" height="60" /></a></div>
                        <div class="title">
                        	<div class="tit"><a href="javascript:;" ><?php echo ($data["title"]); ?></a></div>
                            <div class="num">
                            	<div class="style_num1">数量：<span><?php echo ($data["num"]); ?></span>&nbsp;&nbsp;<?php if(($data["kucun"]) == "0"): ?><span style="color:#f00;">库存不足</span><?php else: if(($data["kucun"]) < $data['num']): ?><span style="color:#f00;">仅剩：<?php echo ($data["kucun"]); ?></span><?php endif; endif; ?></div>
                                <div class="style_num2" style="display:none;">
                                	<form class="numedit" id="<?php echo ($data["pro_id"]); ?>">
                                    	<a href="javascript:;" class="jian" title="<?php echo ($data["pro_id"]); ?>">-</a>
                                        <input type="text" class="numtxt"  value="<?php echo ($data["num"]); ?>" title="<?php echo ($data["pro_id"]); ?>" />
                                        <a href="javascript:;" class="jia" title="<?php echo ($data["pro_id"]); ?>">+</a>
                                        <input type="hidden" class="pro_id" value="<?php echo ($data["id"]); ?>" />
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="price"><span>￥<?php echo ($data[price]); ?></span></div>
                    </div>
                </div>
                <div class="xiaoji">小计:<span>￥<code><?php echo $data['num']*$data['price']; ?></code></span></div>
                
            </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
</div>
<div class="jiesuan">
    <div class="countprice">总计:<span>￥<code><?php echo ($money); ?></code></span></div>
    <a href="javascript:;" class="gojs" >去结算(<span><?php echo ($prod_length); ?></span>)</a>
    <div class="delthis" style="display:none;"><a href="javascript:;"><span class="icon-trash"></span>删除</a></div>
</div>
<form>
<script>
$(function(){
	$('input').iCheck({
    	checkboxClass: 'icheckbox_flat-red',
    	increaseArea: '20%' // optional
  	});
	
	$('.allstatus').on('ifChecked', function(event){
		$(".status").iCheck('check');
	});
	$('.allstatus').on('ifUnchecked', function(event){
	  $(".status").iCheck('uncheck');
	});
	
	$(".btn").click(function(){
		//var c = $(this).find("input").attr("checked");
		$(this).find("input").iCheck('toggle');
		
		
	});
	
	$('.status').on('ifChecked', function(event){
		
		/* 修改总计金额 */
		var xiaoji = $(this).parent().parent().parent().parent().find(".xiaoji code").html();
		xiaoji = Number(xiaoji);
		var countprice = $(".countprice code").html();
		countprice = Number(countprice);
		var newprice = countprice + xiaoji;
		newprice = newprice.toFixed(2);
		$(".countprice code").html(newprice);
		/* 修改总计数量 */
		var length = $(this).parent().parent().parent().parent().find(".style_num1 span").html();
		length = Number(length);
		var count = $(".gojs span").html();
		count = Number(count);
		
		var newcount = count + length;
		$(".gojs span").html(newcount);
	});
	
	$('.status').on('ifUnchecked', function(event){
		var xiaoji = $(this).parent().parent().parent().parent().find(".xiaoji code").html();
		xiaoji = Number(xiaoji);
		var countprice = $(".countprice code").html();
		countprice = Number(countprice);
		var newprice = countprice - xiaoji;
		newprice = newprice.toFixed(2);
		$(".countprice code").html(newprice);
		
		/*  */
		var length = $(this).parent().parent().parent().parent().find(".style_num1 span").html();
		length = Number(length);
		var count = $(".gojs span").html();
		count = Number(count);
		
		var newcount = count - length;
		$(".gojs span").html(newcount);
		
	});
	
	$(".gojs").click(function(){
		$(".cartform").submit();
	});
	
	//计算标题宽度
	var width = $(window).width();
	if(width>600) width=600;
	width = width - 195;
	$(".lists li .title").width(width);
	
	$(".delthis").click(function(){
		if(confirm("确定从购物车删除？")){
			var id = "";
			$(".status").each(function(index, element) {
            	if($(this).is(":checked")){
					id = id + $(this).val() + "," ;
				}
        	});
			var url = "<?php echo U('forDelete');?>" + "?id=" + id;
			window.location.href = url;
		}
	});
	
	var lock = false;//链接锁
	//编辑按钮
	$(".editcart").click(function(){
		var thisname = $(this).html();
		if(thisname=="编辑"){
			var countprice = $(".countprice").html();
		$(".countprice").hide();
		$(".gojs").hide();
		$(".delthis").show();
		$(".cart li .style_num1").hide();
		$(".cart li .style_num2").show();
		$(this).html("完成");
		$(this).removeClass("editcart").addClass("done");
		lock = true;
		}else{
			location.href="";
		}
	});
	
	$(".numtxt").focus(function(){
		$(this).width(70);
	});
	
	$(".numtxt").blur(function(){
		var num = $(this).val();
		num = Number(num);
		var oldnum = $(this).parent().parent().parent().parent().find(".style_num1 span").html();
		oldnum = Number(oldnum);
		var id = $(this).attr("title");
		if(!num || num<=0){
			alert("数量不能小于0");
		}else{
			//num = num - oldnum;
			var url = "<?php echo U('cart/update');?>?pro_id="+id+"&num="+num;
			$.get(url,function(){});
			$(this).width(30);
		}
	});
	
	//增加数量按钮
	$(".jia").click(function(){
		var price = $(this).parent().parent().parent().parent().parent().find(".price span").html();//获取单价
		price = price.substring(1);
		price = Number(price);
		var num = $(this).parent().find(".numtxt");
		var id = $(this).attr("title");
		var nownum = Number(num.val()) + 1;
		var url = "<?php echo U('cart/update');?>?pro_id="+id+"&num="+nownum;
		$.get(url,function(){});
		num.val(nownum);
	});
	$(".jian").click(function(){
		var price = $(this).parent().parent().parent().parent().parent().find(".price span").html();//获取单价
		price = price.substring(1);
		price = Number(price);
		var num = $(this).parent().find(".numtxt");
		var id = $(this).attr("title");
		var nownum = Number(num.val()) - 1;
		if(nownum>=1){
			var url = "<?php echo U('cart/update');?>?pro_id="+id+"&num="+nownum;
			$.get(url,function(){});
			num.val(nownum);
			var xiaoji = Number(nownum * price).toFixed(2);
			var duo = Number(nownum);
		}
	});
})

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