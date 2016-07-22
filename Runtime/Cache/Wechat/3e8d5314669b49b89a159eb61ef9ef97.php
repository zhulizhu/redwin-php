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
	
    <!--点击首页快速导航获取参数值-->
    <script type="text/javascript">
        function getParameter(param)
        {
            var query = window.location.search;//获取URL地址中？后的所有字符
            var iLen = param.length;//获取你的参数名称长度
            var iStart = query.indexOf(param);//获取你该参数名称的其实索引
            if (iStart == -1)//-1为没有该参数
                return "";
            iStart += iLen + 1;
            var iEnd = query.indexOf("&", iStart);//获取第二个参数的其实索引
            if (iEnd == -1)//只有一个参数
                return query.substring(iStart);//获取单个参数的参数值
            return query.substring(iStart, iEnd);//获取第二个参数的值
        }
        function init() {
            var param = getParameter("id");//获取到id后面的值 (one,nine...)
            if(param=="one"){
                $("#one").addClass('public_pop').parent().siblings().children().removeClass('public_pop');
                $(".country").show();
            }
            else if(param=="nine"){
                $("#nine").addClass('public_pop').parent().siblings().children().removeClass('public_pop');
                $(".tinct").show();
            }
            else if(param=="eight"){
                $("#eight").addClass('public_pop').parent().siblings().children().removeClass('public_pop');
                $(".grape").show();
            }
            else if(param=="twelve"){
                $("#twelve").addClass('public_pop').parent().siblings().children().removeClass('public_pop');
                $(".bubbly").show();
            }
            else if(param=="thirteen"){
                $("#thirteen").addClass('public_pop').parent().siblings().children().removeClass('public_pop');
                $(".country").hide();
            }
            else if(param=="ten"){
                $("#ten").addClass('public_pop').parent().siblings().children().removeClass('public_pop');
                $(".taste").show();
            }
            else if(param=="elev"){
                $("#elev").addClass('public_pop').parent().siblings().children().removeClass('public_pop');
                $(".wine_set").show();
            }
        }
    </script>
</head>
<body onload="init()">
<div id="category">
    <div class="cate">
        <div>
            <a href="javascript:;">
                <img src="/Public/Wechat/images/kdkdftu_03.png" alt="" style="width: 47px;height: 19px;margin: 1px -12px 1px 3px;
   			 padding: 0;">全部分类<img src="/Public/Wechat/images/kdkdftu_06.png" alt="" style="width: 17px;
    			height: 17px;
   				 bottom: 3px;
   			 	right: 0;
    			position: absolute;">
            </a>
        </div>
        <input type="text" placeholder="请输入商品名称">
        <a href="product_list.html">
            <img src="/Public/Wechat/images/tyt_06.png" alt="" style="width:30px;height:30px;vertical-align: middle;float: right;
    margin: 10px 2px 1px 1px;">
        </a>

    </div>
    <!--<div class="bot">-->
        <!--<div class="lef_tu">-->
            <!--<img src="/Public/Wechat/images/hfhfh_05.png" alt="">-->
        <!--</div>-->
        <!--<div class="rig_text">-->
            <!--<h5><a href="">忙碌是时间给自己最好的礼物</a></h5>-->

            <!--<p><a href="">Action speak louder than words.Action speak louder than words.</a></p>-->
			<!--<span style="position: absolute;bottom:12px;"><a href="">2006年</a>-->
				<!--<a href="">意大利</a>-->
				<!--<a href="" style="color:red">￥889</a>-->
			<!--</span>-->
            <!--<a href="">-->
                <!--<img src="/Public/Wechat/images/kdkdftu_11.png" alt=""-->
                     <!--style="width:27px;height:30px;float:right;bottom: 20px;right: 10px; position: absolute;">-->
            <!--</a>-->
        <!--</div>-->
    <!--</div>-->
    <!--<div class="bot">-->
        <!--<div class="lef_tu">-->
            <!--<img src="/Public/Wechat/images/hfhfh_05.png" alt="">-->
        <!--</div>-->
        <!--<div class="rig_text">-->
            <!--<h5><a href="">忙碌是时间给自己最好的礼物</a></h5>-->

            <!--<p><a href="">Action speak louder than words.Action speak louder than words.</a></p>-->
			<!--<span style="position: absolute;bottom:12px;">-->
                <!--<a href="">2006年</a>-->
				<!--<a href="">意大利</a>-->
				<!--<a href="" style="color:red">￥889</a>-->
			<!--</span>-->
            <!--<a href=""><img src="/Public/Wechat/images/kdkdftu_11.png" alt=""-->
                            <!--style="width:27px;height:30px;float:right;bottom: 20px;right: 10px; position: absolute;"></a>-->
        <!--</div>-->
    <!--</div>-->
    <!-- 展现全部分类 -->

    <div class="pop_cate" id="pop_cate">
        <div>
            <div class="pop_lef" id="123">
                <ul>
                    <a href="javascript:;">
                        <li class="public_pop" id="one"><img src="/Public/Wechat/images/ghsgsg.png" alt="">国家<span>&gt;</span>
                        </li>
                    </a>
                    <a href="javascript:;">
                        <li id="two"><img src="/Public/Wechat/images/mcb.png" alt="">价格<span>&gt;</span></li>
                    </a>
                    <a href="javascript:;">
                        <li id="three"><img src="/Public/Wechat/images/hjf.png" alt="">整箱<span>&gt;</span></li>
                    </a>
                    <a  href="product_list.html">
                        <li id="four"><img src="/Public/Wechat/images/jd.png" alt="">单支</li>
                    </a>
                    <a href="javascript:;">
                        <li id="five"><img src="/Public/Wechat/images/sgds.png" alt="">场合选酒<span>&gt;</span></li>
                    </a>
                    <a href="product_list.html">
                        <li id="six"><img src="/Public/Wechat/images/ghsgsg.png" alt="">酒庄分类</li>
                    </a>
                    <a href="javascript:;">
                        <li id="seven"><img src="/Public/Wechat/images/sgd.png" alt="">种类分类<span>&gt;</span></li>
                    </a>
                    <a href="javascript:;">
                        <li id="eight"><img src="/Public/Wechat/images/sfd.png" alt="">葡萄种类<span>&gt;</span></li>
                    </a>
                    <a href="javascript:;">
                        <li id="nine"><img src="/Public/Wechat/images/yanse1.png" alt="">颜色<span>&gt;</span></li>
                    </a>
                    <a href="javascript:;">
                        <li id="ten"><img src="/Public/Wechat/images/kougan1.png" alt="">口感<span>&gt;</span></li>
                    </a>
                    <a href="javascript:;">
                        <li id="elev"><img src="/Public/Wechat/images/jiuju1.png" alt="">酒具<span>&gt;</span></li>
                    </a>
                    <a href="javascript:;">
                        <li id="twelve"><img src="/Public/Wechat/images/xiangbing1.png" alt="">香槟.起泡<span>&gt;</span></li>
                    </a>
                    <a href="product_list.html">
                        <li id="thirteen"><img src="/Public/Wechat/images/jiaqiang.png" alt="">加强型</li>
                    </a>
                </ul>
            </div>
            <!--右边country-->
            <div class="pop_rig country">
                <ul>
                    <a href="product_list.html">
                        <li>法国</li>
                    </a>
                    <a href="product_list.html">
                        <li>意大利</li>
                    </a>
                    <a href="product_list.html">
                        <li>德国</li>
                    </a>
                    <a href="product_list.html">
                        <li>西班牙</li>
                    </a>
                    <a href="product_list.html">
                        <li>葡萄牙</li>
                    </a>
                    <a href="product_list.html">
                        <li>美国</li>
                    </a>
                    <a href="product_list.html">
                        <li>澳大利亚</li>
                    </a>
                    <a href="product_list.html">
                        <li>南非</li>
                    </a>
                    <a href="product_list.html">
                        <li>智利</li>
                    </a>
                    <a href="product_list.html">
                        <li>其他</li>
                    </a>
                </ul>
            </div>
            <!--价格-->
            <div class="pop_rig price">
                <ul>
                    <a href="product_list.html">
                        <li>100</li>
                    </a>
                    <a href="product_list.html">
                        <li>100-199</li>
                    </a>
                    <a href="product_list.html">
                        <li>200-299</li>
                    </a>
                    <a href="product_list.html">
                        <li>300-399</li>
                    </a>
                    <a href="product_list.html">
                        <li>等</li>
                    </a>
                </ul>
            </div>
            <!--整箱-->
            <div class="pop_rig box">
                <ul>
                    <a href="product_list.html">
                        <li>双支礼盒装</li>
                    </a>
                    <a href="product_list.html">
                        <li>整箱6支装</li>
                    </a>
                    <a href="product_list.html">
                        <li>整箱12支装</li>
                    </a>
                </ul>
            </div>
            <!--场合选酒-->
            <div class="pop_rig choose">
                <ul>
                    <a href="product_list.html">
                        <li>婚宴用酒</li>
                    </a>
                    <a href="product_list.html">
                        <li>聚会party</li>
                    </a>
                    <a href="product_list.html">
                        <li>送礼用酒</li>
                    </a>
                    <a href="product_list.html">
                        <li>商务用酒</li>
                    </a>
                    <a href="product_list.html">
                        <li>收藏用酒</li>
                    </a>
                </ul>
            </div>
            <!--种类分类-->
            <div class="pop_rig variety">
                <ul>
                    <a href="product_list.html">
                        <li>干红葡萄酒</li>
                    </a>
                    <a href="product_list.html">
                        <li>起泡酒
                        </li>
                    </a>
                    <a href="product_list.html">
                        <li>玫瑰红葡萄酒</li>
                    </a>
                    <a href="product_list.html">
                        <li>加强型葡萄酒</li>
                    </a>
                </ul>
            </div>
            <!--葡萄种类-->
            <div class="pop_rig grape">
                <ul>
                    <a href="product_list.html">
                        <li>赤霞珠 Cabernet</li>
                    </a>
                    <a href="product_list.html">
                        <li>梅洛 Merlot</li>
                    </a>
                    <a href="product_list.html">
                        <li>西拉子 Shiraz</li>
                    </a>
                    <a href="product_list.html">
                        <li>巴贝拉 Barbera</li>
                    </a>
                    <a href="product_list.html">
                        <li>黑皮诺 Pinot Noir</li>
                    </a>
                    <a href="product_list.html">
                        <li>桑娇维塞 Sangiovese</li>
                    </a>
                    <a href="product_list.html">
                        <li>仙粉黛 Zinfandel</li>
                    </a>
                    <a href="product_list.html">
                        <li>丹魄 Tempranillo</li>
                    </a>
                    <a href="product_list.html">
                        <li>其他</li>
                    </a>
                </ul>
            </div>
            <!--颜色-->
            <div class="pop_rig tinct">
                <ul>
                    <a href="product_list.html">
                        <li>红</li>
                    </a>
                    <a href="product_list.html">
                        <li>白</li>
                    </a>
                    <a href="product_list.html">
                        <li>桃红</li>
                    </a>
                </ul>
            </div>
            <!--口感-->
            <div class="pop_rig taste">
                <ul>
                    <a href="product_list.html">
                        <li>干</li>
                    </a>
                    <a href="product_list.html">
                        <li>半干</li>
                    </a>
                    <a href="product_list.html">
                        <li>甜</li>
                    </a>
                    <a href="product_list.html">
                        <li>半甜</li>
                    </a>
                </ul>
            </div>
            <!--酒具-->
            <div class="pop_rig wine_set">
                <ul>
                    <a href="product_list.html">
                        <li>开瓶器</li>
                    </a>
                    <a href="product_list.html">
                        <li>酒杯</li>
                    </a>
                    <a href="product_list.html">
                        <li>酒塞</li>
                    </a>
                    <a href="product_list.html">
                        <li>酒盒</li>
                    </a>
                    <a href="product_list.html">
                        <li>倒酒器</li>
                    </a>
                    <a href="product_list.html">
                        <li>醒酒器</li>
                    </a>
                    <a href="product_list.html">
                        <li>酒鼻子</li>
                    </a>
                    <a href="product_list.html">
                        <li>酒架</li>
                    </a>
                    <a href="product_list.html">
                        <li>其他</li>
                    </a>
                </ul>
            </div>
            <!--香槟气泡-->
            <div class="pop_rig bubbly">
                <ul>
                    <a href="product_list.html">
                        <li>香槟</li>
                    </a>
                    <a href="product_list.html">
                        <li>气泡</li>
                    </a>
                </ul>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $(".pop_cate").show();
        $(".pop_rig").hide();
        $(".country").show();

        $("#one").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".country").show();
            $(".price").hide();
            $(".box").hide();
            $(".choose").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".taste").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#two").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".price").show();
            $(".country").hide();
            $(".box").hide();
            $(".choose").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".taste").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#three").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".box").show();
            $(".country").hide();
            $(".price").hide();
            $(".choose").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".taste").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#four").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
        });
        $("#five").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".choose").show();
            $(".country").hide();
            $(".price").hide();
            $(".box").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".taste").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#six").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
        });
        $("#seven").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".variety").show();
            $(".country").hide();
            $(".price").hide();
            $(".box").hide();
            $(".choose").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".taste").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#eight").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".grape").show();
            $(".country").hide();
            $(".price").hide();
            $(".box").hide();
            $(".choose").hide();
            $(".variety").hide();
            $(".tinct").hide();
            $(".taste").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#nine").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".tinct").show();
            $(".country").hide();
            $(".price").hide();
            $(".choose").hide();
            $(".box").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".taste").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#ten").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".taste").show();
            $(".country").hide();
            $(".price").hide();
            $(".choose").hide();
            $(".box").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".wine_set").hide();
            $(".bubbly").hide();
        });
        $("#elev").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".wine_set").show();
            $(".country").hide();
            $(".price").hide();
            $(".choose").hide();
            $(".box").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".taste").hide();
            $(".bubbly").hide();
        });
        $("#twelve").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
            $(".bubbly").show();
            $(".wine_set").hide();
            $(".country").hide();
            $(".price").hide();
            $(".choose").hide();
            $(".box").hide();
            $(".variety").hide();
            $(".grape").hide();
            $(".tinct").hide();
            $(".taste").hide();
        });
        $("#thirteen").click(function (event) {
            $(this).addClass('public_pop').parent().siblings().children().removeClass('public_pop');
        });

        $(".pop_rig>ul>a>li").click(function (event) {
            $(this).addClass('public_pop').siblings().children().removeClass('public_pop');
        });
        $("#zong").click(function () {
            $(this).addClass('public_li').parent().siblings().children().removeClass('public_li');
            /*	$(".pop_div").show(300);*/
            $(".pop_div").slideToggle(300);
        });
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