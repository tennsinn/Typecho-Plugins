# Words

一个Typecho的简单碎语插件

##Instructions

直接使用模板样式输出碎语分页`Words_Plugin::render()`

单独获取碎语内容`$words = Typecho_Widget::widget('Words_Action')->getWords()`（默认参数 $type='page', $pageSize=10）
>$type 获取模式
>* 'page'按$pageSize大小获取分页，返回数组：键'words'碎语条目数组；键'nav'分页盒，可用`$words['nav']->render(_t('&laquo;'), _t('&raquo;'))`输出
>* 'latest'最近一条碎语内容
>* 'single'按请求值获取一条碎语内容
>* 'all'全部碎语条目

单独获取评论内容`$comments = Typecho_Widget::widget('Words_Action')->getComments()`(默认参数 $type='page', $wid=NULL, $cid=NULL, $pageSize=10)
>$type 获取模式
>* 'page'按$pageSize大小获取分页，返回数组：键'comments'评论条目数组；键'nav'分页盒，可用`$comments['nav']->render(_t('&laquo;'), _t('&raquo;'))`输出
>* 'related'获取$wid代表碎语条目的所有评论
>* 'single'按$cid值获取一条评论内容
>* 'all'全部评论条目

##Changelog

###1.1.1
* 修复后台评论筛选问题
* 增加后台评论跳转碎语链接

###1.1.0
* 修复时间显示错误
* 修复后台表格提交错误
* 修改默认样式中回复按钮
* 增加删除评论时同时删除回复的评论
* 增加插件开启判断
* 增加邮件提醒功能