Bangumi
======================

一个利用Bangumi信息的ACG本地整理Typecho插件

**一个毫无意义的Typecho插件**

Instructions
------------

记录信息可在新增中使用搜索获取到Bangumi.tv上的对应搜索项目进行添加

可在新增中使用同步按钮根据设置中保存的bangumi id获取Bangumi.tv上的正在观看的最近50条记录

注：此插件仅能获取Bangumi.tv上的部分信息，无法进行同步管理

`Bangumi_Plugin::render();` 模板输出

`$bangumis = Typecho_Widget::widget('Bangumi_Action')->getCollection();` 仅获取数据（格式array(array(item => data))）

Changelog
---------