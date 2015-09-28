# Collection

**一个不明意义的Typecho插件**

模仿Bangumi同时利用其部分信息进行本地收藏整理并且展示

## Instructions

通过新增中使用搜索获取到Bangumi.tv上的对应项目添加记录信息

可使用同步按钮根据设置中保存的bangumi uid获取到Bangumi.tv上正在观看的最近50条记录

注：此插件仅能获取Bangumi.tv上的部分信息，无法同步Bangumi进行管理

`Collection_Plugin::render();` 预设模板输出

`$collections = Typecho_Widget::widget('Collection_Action')->getCollection($pageSize);` 根据请求的collection, type等参数返回相应的记录条目，$pageSize为分页大小（返回格式`array('resault' => ture/false, 'message' => false时的错误信息, 'list' => 记录条目, 'nav' => 分页盒)）