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
            

            
	<script type="text/javascript" src="/Public/static/uploadify/jquery.uploadify.min.js"></script>
	<div class="main-title">
		<h2><?php echo isset($info['id'])?'编辑':'新增';?>分类</h2>
	</div>
	<div class="tab-wrap">
		<ul class="tab-nav nav">
			<li data-tab="tab1" class="current"><a href="javascript:void(0);">基 础</a></li>
			<li data-tab="tab2"><a href="javascript:void(0);">高 级</a></li>
            <?php if(($info["id"]) != ""): ?><li data-tab="tab3"><a href="javascript:void(0);">设 计</a></li><?php endif; ?>
            
		</ul>
		<div class="tab-content">
			<form action="<?php echo U();?>" method="post" class="form-horizontal">
				<!-- 基础 -->
				<div id="tab1" class="tab-pane in tab1">
					<div class="form-item">
						<label class="item-label">上级分类<span class="check-tips"></span></label>
						<div class="controls">
							<input type="text" class="text input-large" disabled="disabled" value="<?php echo ((isset($category['title']) && ($category['title'] !== ""))?($category['title']):'无'); ?>"/>
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">
							分类名称<span class="check-tips">（名称不能为空）</span>
						</label>
						<div class="controls">
							<input type="text" name="title" class="text input-large" value="<?php echo ((isset($info["title"]) && ($info["title"] !== ""))?($info["title"]):''); ?>">
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">
							分类标识<span class="check-tips">（英文字母）</span>
						</label>
						<div class="controls">
							<input type="text" name="name" class="text input-large" value="<?php echo ((isset($info["name"]) && ($info["name"] !== ""))?($info["name"]):''); ?>">
						</div>
					</div>
                    <script>
                    $(function(){
						$("[name='title']").blur(function(){
							var url = "/Admin/Procate/fanyi/q/"+$(this).val()+".html"
							$.get(url,function(data,status){
    							$("[name='name']").val(data);
  							});
						});
					})
                    </script>
                    <input type="hidden" name="lefttype" value="0" /><!--分类类型-->
                    <input type="hidden" name="group" value="" /><!--分组-->
					<input type="hidden" name="model[]" value="12" />
                    <div class="form-item">
						<label class="item-label">附加文档模型<span class="check-tips">（分类支持发布的附加文档模型）</span></label>
						<div class="controls">
							<?php $_result=get_product_model();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><label class="checkbox">
									<input type="checkbox" name="model_append[]" value="<?php echo ($list["id"]); ?>" ><?php echo ($list["title"]); ?>
								</label><?php endforeach; endif; else: echo "" ;endif; ?>
						</div>
					</div>
					
					<div class="controls">
						<label class="item-label">分类图标</label>
						<input type="file" id="upload_picture">
						<input type="hidden" name="icon" id="icon" value="<?php echo ((isset($info['icon']) && ($info['icon'] !== ""))?($info['icon']):''); ?>"/>
						<div class="upload-img-box">
						<?php if(!empty($info['icon'])): ?><div class="upload-pre-item"><img src="<?php echo (get_cover($info["icon"],'path')); ?>"/></div><?php endif; ?>
						</div>
					</div>
                    <div class="form-item">
						<label class="item-label">
							图标链接
						</label>
						<div class="controls">
							<input type="text" name="url" class="text input-large" value="<?php echo ((isset($info["url"]) && ($info["url"] !== ""))?($info["url"]):''); ?>">
						</div>
					</div>
					<script type="text/javascript">
					
					//上传图片
				    /* 初始化上传插件 */
					$("#upload_picture").uploadify({
				        "height"          : 30,
				        "swf"             : "/Public/static/uploadify/uploadify.swf",
				        "fileObjName"     : "download",
				        "buttonText"      : "上传图片",
				        "uploader"        : "<?php echo U('File/uploadPicture',array('session_id'=>session_id()));?>",
				        "width"           : 120,
				        'removeTimeout'	  : 1,
				        'fileTypeExts'	  : '*.jpg; *.png; *.gif;',
				        "onUploadSuccess" : uploadPicture,
				        'onFallback' : function() {
				            alert('未检测到兼容版本的Flash.');
				        }
				    });
					function uploadPicture(file, data){
				    	var data = $.parseJSON(data);
				    	var src = '';
				        if(data.status){
				        	$("#icon").val(data.id);
				        	src = data.url || '' + data.path;
				        	$("#icon").parent().find('.upload-img-box').html(
				        		'<div class="upload-pre-item"><img src="' + src + '"/></div>'
				        	);
				        } else {
				        	updateAlert(data.info);
				        	setTimeout(function(){
				                $('#top-alert').find('button').click();
				                $(that).removeClass('disabled').prop('disabled',false);
				            },1500);
				        }
				    }
					</script>
				</div>

				<!-- 高级 -->
				<div id="tab2" class="tab-pane tab2">
                	<div class="form-item">
						<label class="item-label">
							发布内容<span class="check-tips">（是否允许发布内容）</span>
						</label>
						<div class="controls">
							<label class="inline radio"><input type="radio" name="allow_publish" value="0">不允许</label>
							<label class="inline radio"><input type="radio" name="allow_publish" value="1" checked>仅允许后台</label>
							<label class="inline radio"><input type="radio" name="allow_publish" value="2" >允许前后台</label>
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">
							是否审核<span class="check-tips">（在该分类下发布的内容是否需要审核）</span>
						</label>
						<div class="controls">
							<label class="inline radio"><input type="radio" name="check" value="0" checked>不需要</label>
							<label class="inline radio"><input type="radio" name="check" value="1">需要</label>
						</div>
					</div>
                    <div class="form-item">
						<label class="item-label">允许文档类型</label>
						<div class="controls">
							<?php $_result=C('DOCUMENT_MODEL_TYPE');if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$type): $mod = ($i % 2 );++$i;?><label class="checkbox">
									<input type="checkbox" name="type[]" value="<?php echo ($key); ?>"><?php echo ($type); ?>
								</label><?php endforeach; endif; else: echo "" ;endif; ?>
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">可见性<span class="check-tips">（是否对用户可见，针对前台）</span></label>
						<div class="controls">
							<select name="display">
								<option value="1">所有人可见</option>
								<option value="0">不可见</option>
								<option value="2">管理员可见</option>
							</select>
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">
							回复<span class="check-tips">（是否允许对内容进行回复，需要详情页模板支持回复显示与提交）</span>
						</label>
						<div class="controls">
							<label class="inline radio"><input type="radio" name="reply" value="1" checked>允许</label>
							<label class="inline radio"><input type="radio" name="reply" value="0">不允许</label>
						</div>
					</div>
					<!-- <div class="form-item reply hidden">
						<label class="item-label">回复绑定的文档模型</label>
						<div class="controls">
							<?php $_result=get_document_model();if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><label class="checkbox">
									<input type="checkbox" name="reply_model[]" value="<?php echo ($list["id"]); ?>"><?php echo ($list["title"]); ?>
								</label><?php endforeach; endif; else: echo "" ;endif; ?>
						</div>
					</div> -->
					<div class="form-item">
						<label class="item-label">
							排序<span class="check-tips">（仅对当前层级分类有效）</span>
						</label>
						<div class="controls">
							<input type="text" name="sort" class="text input-small" value="<?php echo ((isset($info["sort"]) && ($info["sort"] !== ""))?($info["sort"]):0); ?>">
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">
							列表行数
						</label>
						<div class="controls">
							<input type="text" name="list_row" class="text input-small" value="<?php echo ((isset($info["list_row"]) && ($info["list_row"] !== ""))?($info["list_row"]):10); ?>">
						</div>
					</div>

				</div>

				<!-- 高级 -->
				<div id="tab2" class="tab-pane tab2">
					<div class="form-item">
						<label class="item-label">网页标题</label>
						<div class="controls">
							<input type="text" name="meta_title" class="text input-large" value="<?php echo ((isset($info["meta_title"]) && ($info["meta_title"] !== ""))?($info["meta_title"]):''); ?>">
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">关键字</label>
						<div class="controls">
							<label class="textarea input-large">
								<textarea name="keywords"><?php echo ((isset($info["keywords"]) && ($info["keywords"] !== ""))?($info["keywords"]):''); ?></textarea>
							</label>
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">描述</label>
						<div class="controls">
							<label class="textarea input-large">
								<textarea name="description"><?php echo ((isset($info["description"]) && ($info["description"] !== ""))?($info["description"]):''); ?></textarea>
							</label>
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">频道模板</label>
						<div class="controls">
							<input type="text" name="template_index" class="text input-large" value="<?php echo ((isset($info["template_index"]) && ($info["template_index"] !== ""))?($info["template_index"]):''); ?>">
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">列表模板</label>
						<div class="controls">
							<input type="text" name="template_lists" class="text input-large" value="<?php echo ((isset($info["template_lists"]) && ($info["template_lists"] !== ""))?($info["template_lists"]):''); ?>">
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">详情模板</label>
						<div class="controls">
							<input type="text" name="template_detail" class="text input-large" value="<?php echo ((isset($info["template_detail"]) && ($info["template_detail"] !== ""))?($info["template_detail"]):''); ?>">
						</div>
					</div>
					<div class="form-item">
						<label class="item-label">编辑模板</label>
						<div class="controls">
							<input type="text" name="template_edit" class="text input-large" value="<?php echo ((isset($info["template_edit"]) && ($info["template_edit"] !== ""))?($info["template_edit"]):''); ?>">
						</div>
					</div>
				</div>
                
                <!--设计-->
                <div id="tab3" class="tab-pane tab3">
					<div class="form-item cf">
						<label class="item-label">表单显示分组<span class="check-tips">（用于表单显示的分组，以及设置该模型表单排序的显示）</span></label>
						<div class="controls">
							<input type="text" class="text input-large" name="field_group" value="<?php echo ($model["field_group"]); ?>">
						</div>
					</div>
					<div class="form-item cf">
					<label class="item-label">表单显示排序<span class="check-tips">（直接拖动进行排序）</span></label>
					<?php $_result=parse_field_attr($model['field_group']);if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="form-item cf edit_sort edit_sort_l form_field_sort">
							<span><?php echo ($vo); ?></span>
							<ul class="dragsort needdragsort" data-group="<?php echo ($key); ?>">
								<?php if(is_array($fields)): foreach($fields as $k=>$field): if((($field['group'] == $key) or($i == 1 and !isset($field['group']))) and ($field['is_show'] == 1)): ?><li class="getSort">
											<em data="<?php echo ($field['id']); ?>"><?php echo ($field['title']); ?> [<?php echo ($field['name']); ?>]</em>
											<input type="hidden" name="field_sort[<?php echo ($key); ?>][]" value="<?php echo ($field['id']); ?>"/>
										</li><?php endif; endforeach; endif; ?>
							</ul>
						</div><?php endforeach; endif; else: echo "" ;endif; ?>
					</div>
				</div>

				<div class="form-item">
                	<input type="hidden" name="model_id" value="<?php echo ($model["id"]); ?>"  />
					<input type="hidden" name="id" value="<?php echo ((isset($info["id"]) && ($info["id"] !== ""))?($info["id"]):''); ?>">
					<input type="hidden" name="pid" value="<?php echo isset($category['id'])?$category['id']:$info['pid'];?>">
					<button type="submit" id="submit" class="btn submit-btn" target-form="form-horizontal">确 定</button>
					<button class="btn btn-return" onclick="javascript:history.back(-1);return false;">返 回</button>
				</div>
			</form>
		</div>
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
    
<script type="text/javascript" src="/Public/static/jquery.dragsort-0.5.1.min.js"></script>
	<script type="text/javascript">
		<?php if(isset($info["id"])): ?>Think.setValue("allow_publish", <?php echo ((isset($info["allow_publish"]) && ($info["allow_publish"] !== ""))?($info["allow_publish"]):'news'); ?>);
		Think.setValue("lefttype", <?php echo ($info["lefttype"]); ?>);
		Think.setValue("check", <?php echo ((isset($info["check"]) && ($info["check"] !== ""))?($info["check"]):0); ?>);
		Think.setValue("model[]", <?php echo ((isset($info["model"]["0"]) && ($info["model"]["0"] !== ""))?($info["model"]["0"]):'2'); ?>);
		Think.setValue("model_append[]", <?php echo (json_encode($info["model_append"])); ?> || [2]);
		Think.setValue("type[]", <?php echo (json_encode($info["type"])); ?> || [2]);
		Think.setValue("display", <?php echo ((isset($info["display"]) && ($info["display"] !== ""))?($info["display"]):'1'); ?>);
		Think.setValue("reply", <?php echo ((isset($info["reply"]) && ($info["reply"] !== ""))?($info["reply"]):0); ?>);
		Think.setValue("reply_model[]", <?php echo (json_encode($info["reply_model"])); ?> || [1]);<?php endif; ?>
		$(function(){
			showTab();
			$("input[name=reply]").change(function(){
				var $reply = $(".form-item.reply");
				parseInt(this.value) ? $reply.show() : $reply.hide();
			}).filter(":checked").change();
			
		});
		//导航高亮
		highlight_subnav('<?php echo U('Procate/index');?>');
	$(".needdragsort").dragsort({
	     dragSelector:'li',
	     placeHolderTemplate: '<li class="draging-place">&nbsp;</li>',
	     dragBetween:true,	//允许拖动到任意地方
	     dragEnd:function(){
	    	 var self = $(this);
	    	 self.find('input').attr('name', 'field_sort[' + self.closest('ul').data('group') + '][]');
	     }
	 });
	</script>

</body>
</html>