<?php
return array (
		'style' => array (
				'title' => '显示样式',
				'tip' => '<a href="http://www.cheewo.com" target="_blank">查看各样式效果</a>',
				'type' => 'select',
				'options' => array (
						'1' => "样式1",
						'2' => "样式2",
						'3' => '样式3' 
				) 
		),
		'place'=>array(
				'title'=>'显示位置',
				'type'=>'radio',
				'options'=>array(
						'0'=>'不显示',
						'left'=>'左侧',
						'right'=>'右侧'
		)
),
		'QQ' => array (
				'title' => 'QQ号',
				'tip' => '（格式：智网客服:121493309 ,一行一个）',
				'type' => 'textarea',
				'value' => '' 
		),
		'fourzz' => array (
				'title' => '400电话',
				'tip' => '(一行一个)',
				'type' => 'textarea',
				'value' => '' 
		),
		'tel' => array (
				'title' => '热线电话',
				'tip' => '(一行一个)',
				'type' => 'textarea',
				'value' => '' 
		) 
);