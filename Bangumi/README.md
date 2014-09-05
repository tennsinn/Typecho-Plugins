Bangumi
=======

一个利用Bangumi信息的ACG本地整理、展示Typecho插件

**一个毫无意义的Typecho插件**

Instructions
------------

记录信息可在新增中使用搜索获取到Bangumi.tv上的对应搜索项目进行添加

可在新增中使用同步按钮根据设置中保存的bangumi id获取Bangumi.tv上的正在观看的最近50条记录

注：此插件仅能获取Bangumi.tv上的部分信息，无法进行同步管理

`Bangumi_Plugin::render();` 模板输出

`$bangumis = Typecho_Widget::widget('Bangumi_Action')->getCollection($pageSize);` 根据请求的collection, type等参数返回相应的记录条目，$pageSize为分页大小（返回格式`array('status' => ture/false, 'message' => false时的错误信息, 'list' => 记录条目, 'nav' => 分页盒)）

Changelog
---------

###1.0.0
建立本地管理