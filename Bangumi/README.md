Typecho_Plugin-Bangumi
======================

一个Typecho的Bangumi收视进度展示插件

**一个毫无意义的Typecho插件**

根据Bangumi的用户id获取用户在看动画信息并展示，开启插件后需要在设置中添加 Bangumi的用户id 才能正常使用，可选择需要保留或使用的信息项目

管理-Bangumi面板仅可进行显示查看和缓存模式下手动更新缓存

`Bangumi_Plugin::render();` 模板输出

`$bangumis = Typecho_Widget::widget('Bangumi_Action')->getBangumi();` 仅获取数据（格式array(id => array(item => data))）