<?php
// +----------------------------------------------------------------------
// | ThinkPHP Socket Business [Socket Business Service For ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkPHP Socket Business 服务
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

/**
 * 事件监听类
 */
class Events extends \think\socket\business\Events
{
    /**
     * 处理类命名空间
     * @var string
     */
    protected static $namespace = '\\app\\example\\handler\\';
}
