<?php if (!defined('THINK_PATH')) exit(); if(is_array($tree)): $i = 0; $__LIST__ = $tree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><dl class="cate-item">
		<dt class="cf">
			<form action="<?php echo U('WechatMenu/edit_sort');?>" method="post">
				<div class="btn-toolbar opt-btn cf">
					<a title="删除" href="<?php echo U('WechatMenu/del?id='.$list['id']);?>" class="confirm ajax-get">删除</a>
				</div>
				<div class="fold"><i></i></div>
				<div class="order"><input type="text" name="sort" class="text input-mini" value="<?php echo ($list["sort"]); ?>"></div>
				<div class="name">
					<span class="tab-sign"></span>
					<input type="hidden" name="id" value="<?php echo ($list["id"]); ?>">
					<input type="text" name="name" class="text" value="<?php echo ($list["name"]); ?>">
                    <a href="<?php echo U('WechatMenu/add?pid='.$list['id']);?>" class="add-sub-cate" >
                    	<i class="icon-add"></i>
                    </a>
                    
					<span class="help-inline msg"></span>
				</div>
			</form>
		</dt>
		<?php if(!empty($list['sub_button'])): ?><dd>
				<?php echo R('Wechat/menutree', array($list['sub_button']));?>
			</dd><?php endif; ?>
	</dl><?php endforeach; endif; else: echo "" ;endif; ?>