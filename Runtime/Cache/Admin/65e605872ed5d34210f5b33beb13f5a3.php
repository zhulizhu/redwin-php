<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ($meta_title); ?>|CheeWoPHP管理平台</title>
    <link href="/Public/favicon.ico" type="image/x-icon" rel="shortcut icon">    
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/base.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/common.css" media="all">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/module.css">
    <link rel="stylesheet" type="text/css" href="/Public/Admin/css/style.css" media="all">
	<link rel="stylesheet" type="text/css" href="/Public/Admin/css/<?php echo (C("COLOR_STYLE")); ?>.css" media="all">
     <!--[if lt IE 9]>
    <script type="text/javascript" src="/Public/static/jquery-1.10.2.min.js"></script>
    <![endif]--><!--[if gte IE 9]><!-->
    <script type="text/javascript" src="/Public/static/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/jquery.mousewheel.js"></script>
    <!--<![endif]-->  
    
</head>
<body>
    <!-- 头部 -->
    <div class="header">
        <!-- Logo -->
        <span class="logo"></span>
        <!-- /Logo -->

        <!-- 主导航 -->
        <ul class="main-nav">
            <?php if(is_array($__MENU__["main"])): $i = 0; $__LIST__ = $__MENU__["main"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li class="<?php echo ((isset($menu["class"]) && ($menu["class"] !== ""))?($menu["class"]):''); ?>"><a href="<?php echo (u($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
        <!-- /主导航 -->

        <!-- 用户栏 -->
        <div class="user-bar">
            <a href="javascript:;" class="user-entrance"><i class="icon-user"></i></a>
            <ul class="nav-list user-menu hidden">
                <li class="manager">你好，<em title="<?php echo session('user_auth.username');?>"><?php echo session('user_auth.username');?></em></li>
                <li><a href="<?php echo U('User/updatePassword');?>">修改密码</a></li>
                <?php if(get_group() == 5): else: ?>
                <li><a href="<?php echo U('User/updateNickname');?>">修改昵称</a></li><?php endif; ?>
                <li><a href="<?php echo U('Public/logout');?>">退出</a></li>
            </ul>
        </div>
    </div>
    <!-- /头部 -->

    <!-- 边栏 -->
    <div class="sidebar">
        <!-- 子导航 -->
        
            <div id="subnav" class="subnav">
                <?php if(!empty($_extra_menu)): ?>
                    <?php echo extra_menu($_extra_menu,$__MENU__); endif; ?>
                <?php if(is_array($__MENU__["child"])): $i = 0; $__LIST__ = $__MENU__["child"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub_menu): $mod = ($i % 2 );++$i;?><!-- 子导航 -->
                    <?php if(!empty($sub_menu)): if(!empty($key)): ?><h3><i class="icon icon-unfold"></i><?php echo ($key); ?></h3><?php endif; ?>
                        <ul class="side-sub-menu">
                            <?php if(is_array($sub_menu)): $i = 0; $__LIST__ = $sub_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li>
                                    <a class="item" href="<?php echo (u($menu["url"])); ?>"><?php echo ($menu["title"]); ?></a>
                                </li><?php endforeach; endif; else: echo "" ;endif; ?>
                        </ul><?php endif; ?>
                    <!-- /子导航 --><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        
        <!-- /子导航 -->
    </div>
    <!-- /边栏 -->

    <!-- 内容区 -->
    <div id="main-content">
        <div id="top-alert" class="fixed alert alert-error" style="display: none;">
            <button class="close fixed" style="margin-top: 4px;">&times;</button>
            <div class="alert-content">这是内容</div>
        </div>
        <div id="main" class="main">
            
            <!-- nav -->
            <?php if(!empty($_show_nav)): ?><div class="breadcrumb">
                <span>您的位置:</span>
                <?php $i = '1'; ?>
                <?php if(is_array($_nav)): foreach($_nav as $k=>$v): if($i == count($_nav)): ?><span><?php echo ($v); ?></span>
                    <?php else: ?>
                    <span><a href="<?php echo ($k); ?>"><?php echo ($v); ?></a>&gt;</span><?php endif; ?>
                    <?php $i = $i+1; endforeach; endif; ?>
            </div><?php endif; ?>
            <!-- nav -->
            

            
	<!-- 标题栏 -->
	<div class="main-title">
		<h2>用户列表<?php if(($nowgroupid) > "11"): if(($nowgroupid) < "14"): ?>&nbsp;&nbsp;（我的消费:<?php echo (get_yeji_by_uid($nowuid)); ?>，团队业绩:<?php echo (get_yeji($nowuid)); ?>，总业绩:<?php echo get_yeji_by_uid($nowuid)+get_yeji($nowuid); ?>）<?php endif; endif; if(($nowgroupid) == "16"): ?>（线上余额总计：￥<?php echo ($countmoney); ?>）<?php endif; ?></h2>
	</div>
	<div class="cf">
    	<?php if(($nowgroupid) > "13"): ?><div class="fl">
            <a class="btn" href="<?php echo U('User/add');?>">新 增</a>
            <button class="btn ajax-post" url="<?php echo U('User/changeStatus',array('method'=>'resumeUser'));?>" target-form="ids">启 用</button>
            <button class="btn ajax-post" url="<?php echo U('User/changeStatus',array('method'=>'forbidUser'));?>" target-form="ids">禁 用</button>
            <button class="btn ajax-post confirm" url="<?php echo U('User/changeStatus',array('method'=>'deleteUser'));?>" target-form="ids">删 除</button>
            <select class="sel_group">
            	<option value="/Admin/User/index" >所有分组</option>
                <?php if(is_array($grouplist)): $i = 0; $__LIST__ = $grouplist;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><option value="<?php echo U('index?group='.$list[id]);?>" <?php if($thisgroup == $list[id]): ?>selected="selected"<?php endif; ?> > <?php echo ($list["title"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
            </select>
        </div>
        
        <script>
        $(function(){
			$(".sel_group").change(function(){
				var value = $(this).val();
				location.href=value;
			})
		})
        </script>

        <!-- 高级搜索 -->
		<div class="search-form fr cf">
        	<div class="fr cf">
                <a class="btn confirm" href="<?php echo U('User/exportRadio',array('method'=>'dcRadio'));?>">导出</a>
			</div>
            
            
			<div class="btn-group-click adv-sch-pannel fr"  style="margin-right:20px;">
                <button class="btn">高 级<i class="btn-arrowdown"></i></button>
                <div class="dropdown cf">
                	<div class="row">
                		<label>注册时间：</label>
                		<input type="text" id="time-start" name="time-start" class="text input-2x" value="" placeholder="起始时间" /> -
                		<input type="text" id="time-end" name="time-end" class="text input-2x" value="" placeholder="结束时间" />
                	</div>
                	<div class="row">
                		<label>昵称查询：</label>
                		<input type="text" name="nickname" class="text input-2x" value="<?php echo I('nickname');?>" placeholder="请输入用户昵称">
                	</div>
                    <div class="row">
                		<label>电话查询：</label>
                		<input type="text" name="mobile" class="text input-2x" value="<?php echo I('mobile');?>" placeholder="请输入用户电话">
                	</div>
                </div>
            </div>
            <div class="sleft fr cf">
				<input type="text" name="uid" class="search-input" value="<?php echo I('uid');?>" placeholder="请输入用户编号">
				<a class="sch-btn" href="javascript:;" id="search" url="<?php echo U('index');?>"><i class="btn-search"></i></a>
			</div>
            
		</div><?php endif; ?>
    </div>
    <!-- 数据列表 -->
    <div class="data-table table-striped">
	<table class="">
    <thead>
        <tr>
		<th class="row-selected row-selected"><input class="check-all" type="checkbox"/></th>
		<th class="">UID</th>
        <th class="">头像</th>
		<th class="">昵称</th>
        <th class="">电话</th>
        <th class="">用户组</th>
        <th class="">上级分组</th>
        <th class="">我的余额</th>
        <th class="">业绩积分</th>
        <?php if(($nowgroupid) != "13"): if(($nowgroupid) != "12"): ?><th>扣除</th><?php endif; endif; ?>
		<th class="">个人业绩</th>
		<th class="">团队业绩</th>
        <th class="">状态</th>
		<th class="">注册时间</th>
        <th class="">查看</th>
		<th class="">操作</th>
		</tr>
    </thead>
    <tbody>
		<?php if(!empty($_list)): if(is_array($_list)): $i = 0; $__LIST__ = $_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
            <td><input class="ids" type="checkbox" name="id[]" value="<?php echo ($vo["uid"]); ?>" /></td>
			<td><?php echo ($vo["uid"]); ?> </td>
            <td><img src="<?php if(empty($vo["headimgurl"])): ?>/Public/Admin/images/getheadimg.jpg<?php else: echo ($vo["headimgurl"]); endif; ?>" width="40" height="40" style=" border-radius:5px;" /></td>
			<td><?php echo ($vo["nickname"]); ?></td>
            <td><?php echo ($vo["mobile"]); ?></td>
            <td><?php echo ($vo["group"]); ?></td>
            <td><?php echo ($vo["upgroup"]); ?></td>
            <td><label style="float:left;">￥<?php echo ($vo["money"]); ?></label></td>
            <td><label style="float:left;">￥<?php echo ($vo["xxmoney"]); ?></label></td>
            <?php if(($nowgroupid) != "13"): if(($nowgroupid) != "12"): ?><td><form action="<?php echo U('kouchu');?>" method="post" class="form-horizontal<?php echo ($vo["uid"]); ?>" ><input type="text" value="0" name="money" class="text input-small" style="width:50px;" />&nbsp;<input type="hidden" name="uid" value="<?php echo ($vo["uid"]); ?>" /><button type="submit" class="btn confirm ajax-post"  target-form="form-horizontal<?php echo ($vo["uid"]); ?>" >扣除</button></form></td><?php endif; endif; ?>
			<td><?php echo (get_yeji_by_uid($vo["uid"])); ?></td>
			<td><?php echo (get_yeji($vo["uid"])); ?></td>
            <td><?php echo (get_status_title($vo["status"])); ?></td>
			<td><span><?php echo (get_reg_time($vo["uid"],"Y-m-d H:i")); ?></span></td>
            <td>
            <a href="<?php echo U('Order/index');?>?search_uid=<?php echo ($vo["uid"]); ?>">订单</a>&nbsp;&nbsp;&nbsp;
            <a href="<?php echo U('Fy/index?uid='.$vo['uid']);?>" class="confirm">分佣</a>&nbsp;&nbsp;&nbsp;
            <a href="<?php echo U('User/team?uid='.$vo['uid']);?>" class="confirm">团队（<?php echo count(get_all_team($vo['uid'])); ?>）</a></td>
			<td>
            	<?php if(($nowuid) == "1"): ?><a href="<?php echo U('User/edit?id='.$vo['uid']);?>" class="confirm">编辑</a>
                    <?php if(($vo["status"]) == "1"): ?><a href="<?php echo U('User/changeStatus?method=forbidUser&id='.$vo['uid']);?>" class="ajax-get">禁用</a>
                    <?php else: ?>
                    <a href="<?php echo U('User/changeStatus?method=resumeUser&id='.$vo['uid']);?>" class="ajax-get">启用</a><?php endif; ?>
                    <a href="<?php echo U('User/deluser?id='.$vo['uid']);?>" class="confirm ajax-get">删除</a>
                <?php else: ?>
            	<?php if(($nowgroupid) > "13"): ?><a href="<?php echo U('User/edit?id='.$vo['uid']);?>" class="confirm">编辑</a>
				<?php if(($vo["status"]) == "1"): ?><a href="<?php echo U('User/changeStatus?method=forbidUser&id='.$vo['uid']);?>" class="ajax-get">禁用</a>
				<?php else: ?>
				<a href="<?php echo U('User/changeStatus?method=resumeUser&id='.$vo['uid']);?>" class="ajax-get">启用</a><?php endif; ?>
				<a href="<?php echo U('User/deluser?id='.$vo['uid']);?>" class="confirm ajax-get">删除</a><?php endif; endif; ?>
                </td>
		</tr><?php endforeach; endif; else: echo "" ;endif; ?>
		<?php else: ?>
		<td colspan="16" class="text-center"> aOh! 暂时还没有内容! </td><?php endif; ?>
	</tbody>
    </table>
	</div>
    <div class="page">
        <?php echo ($_page); ?>
    </div>

        </div>
        <div class="cont-ft">
            <div class="copyright">
                <div class="fl">感谢使用<a href="http://www.cheewo.com" target="_blank">CheeWoPHP</a>管理平台</div>
                <div class="fr">V<?php echo (ONETHINK_VERSION); ?></div>
            </div>
        </div>
    </div>
    <!-- /内容区 -->
    <script type="text/javascript">
    (function(){
        var ThinkPHP = window.Think = {
            "ROOT"   : "", //当前网站地址
            "APP"    : "", //当前项目地址
            "PUBLIC" : "/Public", //项目公共目录地址
            "DEEP"   : "<?php echo C('URL_PATHINFO_DEPR');?>", //PATHINFO分割符
            "MODEL"  : ["<?php echo C('URL_MODEL');?>", "<?php echo C('URL_CASE_INSENSITIVE');?>", "<?php echo C('URL_HTML_SUFFIX');?>"],
            "VAR"    : ["<?php echo C('VAR_MODULE');?>", "<?php echo C('VAR_CONTROLLER');?>", "<?php echo C('VAR_ACTION');?>"]
        }
    })();
    </script>
    <script type="text/javascript" src="/Public/static/think.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/common.js"></script>
    <script type="text/javascript">
        +function(){
            var $window = $(window), $subnav = $("#subnav"), url;
            $window.resize(function(){
                $("#main").css("min-height", $window.height() -130);
            }).resize();

            /* 左边菜单高亮 */
            url = window.location.pathname + window.location.search;
            url = url.replace(/(\/(p)\/\d+)|(&p=\d+)|(\/(id)\/\d+)|(&id=\d+)|(\/(group)\/\d+)|(&group=\d+)/, "");
            $subnav.find("a[href='" + url + "']").parent().addClass("current");

            /* 左边菜单显示收起 */
            $("#subnav").on("click", "h3", function(){
                var $this = $(this);
                $this.find(".icon").toggleClass("icon-fold");
                $this.next().slideToggle("fast").siblings(".side-sub-menu:visible").
                      prev("h3").find("i").addClass("icon-fold").end().end().hide();
            });

            $("#subnav h3 a").click(function(e){e.stopPropagation()});

            /* 头部管理员菜单 */
            $(".user-bar").mouseenter(function(){
                var userMenu = $(this).children(".user-menu ");
                userMenu.removeClass("hidden");
                clearTimeout(userMenu.data("timeout"));
            }).mouseleave(function(){
                var userMenu = $(this).children(".user-menu");
                userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
                userMenu.data("timeout", setTimeout(function(){userMenu.addClass("hidden")}, 100));
            });

	        /* 表单获取焦点变色 */
	        $("form").on("focus", "input", function(){
		        $(this).addClass('focus');
	        }).on("blur","input",function(){
				        $(this).removeClass('focus');
			        });
		    $("form").on("focus", "textarea", function(){
			    $(this).closest('label').addClass('focus');
		    }).on("blur","textarea",function(){
			    $(this).closest('label').removeClass('focus');
		    });

            // 导航栏超出窗口高度后的模拟滚动条
            var sHeight = $(".sidebar").height();
            var subHeight  = $(".subnav").height();
            var diff = subHeight - sHeight; //250
            var sub = $(".subnav");
            if(diff > 0){
                $(window).mousewheel(function(event, delta){
                    if(delta>0){
                        if(parseInt(sub.css('marginTop'))>-10){
                            sub.css('marginTop','0px');
                        }else{
                            sub.css('marginTop','+='+10);
                        }
                    }else{
                        if(parseInt(sub.css('marginTop'))<'-'+(diff-10)){
                            sub.css('marginTop','-'+(diff-10));
                        }else{
                            sub.css('marginTop','-='+10);
                        }
                    }
                });
            }
        }();
    </script>
    
	<script src="/Public/static/thinkbox/jquery.thinkbox.js"></script>
    <link href="/Public/static/datetimepicker/css/datetimepicker.css" rel="stylesheet" type="text/css">
<?php if(C('COLOR_STYLE')=='blue_color') echo '<link href="/Public/static/datetimepicker/css/datetimepicker_blue.css" rel="stylesheet" type="text/css">'; ?>
<link href="/Public/static/datetimepicker/css/dropdown.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="/Public/static/datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script type="text/javascript" src="/Public/static/datetimepicker/js/locales/bootstrap-datetimepicker.zh-CN.js" charset="UTF-8"></script>

	<script type="text/javascript">
	$(function(){
		$('#time-start').datetimepicker({
			format: 'yyyy-mm-dd',
			language:"zh-CN",
			minView:2,
			autoclose:true
		});
	
		$('#time-end').datetimepicker({
			format: 'yyyy-mm-dd',
			language:"zh-CN",
			minView:2,
			autoclose:true
		});
		
		//搜索功能
		$("#search").click(function(){
			var url = $(this).attr('url');
			var query  = $('.search-form').find('input').serialize();
			query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g,'');
			query = query.replace(/^&/g,'');
			if( url.indexOf('?')>0 ){
				url += '&' + query;
			}else{
				url += '?' + query;
			}
			window.location.href = url;
		});
		
		/* 状态搜索子菜单 */
		$(".search-form").find(".drop-down").hover(function(){
			$(this).find(".sub-sch-menu").removeClass("hidden");
		},function(){
			$(this).find(".sub-sch-menu").addClass("hidden");
		});
		$(".sub-sch-menu li").find("a").each(function(){
			$(this).click(function(){
				var text = $(this).text();
				$(this).parent().parent().parent().find(".sch-sort-txt").text(text).attr("data",$(this).attr("value"));
				$(this).parent().parent().parent().find(".sub-sch-menu").addClass("hidden");
			})
		});
		
		
		//回车自动提交
		$('.search-form').find('input').keyup(function(event){
			if(event.keyCode===13){
				$("#search").click();
			}
		});
		
		
	});
    //导航高亮
    highlight_subnav('<?php echo U('User/index');?>');
	</script>

</body>
</html>