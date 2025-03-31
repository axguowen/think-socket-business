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

namespace think\socket\business;

use think\helper\Str;
use GatewayWorker\Lib\Gateway;

class Events
{
    /**
     * 处理类命名空间
     * @var string
     */
    protected static $namespace = '\\app\\socket\\handler\\';

    /**
     * 客户端连接上gateway进程时触发的回调
     * @access public
     * @param string $clientId
     * @return void
     */
    public static function onConnect($clientId)
    {
    }

    /**
     * 客户端连接上gateway完成websocket握手时触发的回调
     * @access public
     * @param string $clientId
     * @param array $data
     * @return void
     */
    public static function onWebSocketConnect($clientId, array $data)
    {
        // 如果不存在requestUri，则直接关闭连接
        if (!isset($data['server']) || !isset($data['server']['REQUEST_URI'])) {
            // 关闭连接
            Gateway::closeClient($clientId);
            // 返回
            return;
        }
        // 解析事件处理类
        $eventHandler = static::parseEventHandler($data['server']['REQUEST_URI']);
        // 如果不存在事件处理类，则直接关闭连接
        if(!class_exists($eventHandler)) {
            // 发送消息
            Gateway::sendToClient($clientId, json_encode([
                'type' => 'error',
                'message' => '事件处理类[' . $eventHandler . ']不存在',
            ], JSON_UNESCAPED_UNICODE));
            // 关闭连接
            Gateway::closeClient($clientId);
            // 返回
            return;
        }
        // 记录事件处理类
        $_SESSION['event_handler'] = $eventHandler;
        // 如果事件处理类方法存在
        if (is_callable($eventHandler . '::onWebSocketConnect')) {
            // 执行事件处理类方法
            call_user_func($eventHandler . '::onWebSocketConnect', $clientId, $data);
        }
    }

    /**
     * 客户端发来数据(Gateway进程收到数据)后触发的回调
     * @access public
     * @param string $clientId
     * @param string $message
     * @return void
     */
    public static function onMessage($clientId, $message)
    {
        // 如果Session中没有事件处理类
        if (!isset($_SESSION['event_handler']) || empty($_SESSION['event_handler'])) {
            // 发送消息
            Gateway::sendToClient($clientId, json_encode([
                'type' => 'error',
                'message' => '未指定事件处理类',
            ], JSON_UNESCAPED_UNICODE));
            // 关闭连接
            Gateway::closeClient($clientId);
            // 返回
            return;
        }
        // 获取事件处理类
        $eventHandler = $_SESSION['event_handler'];
        // 如果不存在事件处理类，则直接关闭连接
        if(!class_exists($eventHandler)) {
            // 发送消息
            Gateway::sendToClient($clientId, json_encode([
                'type' => 'error',
                'message' => '事件处理类[' . $eventHandler . ']不存在',
            ], JSON_UNESCAPED_UNICODE));
            // 关闭连接
            Gateway::closeClient($clientId);
            // 返回
            return;
        }
        // 如果事件处理类方法存在
        if (is_callable($eventHandler . '::onMessage')) {
            // 执行事件处理类方法
            call_user_func($eventHandler . '::onMessage', $clientId, $message);
        }
    }

    /**
     * 客户端与Gateway进程的连接断开时触发
     * @access public
     * @param string $clientId
     * @return void
     */
    public static function onClose($clientId)
    {
        // 如果Session中没有事件处理类
        if (!isset($_SESSION['event_handler']) || empty($_SESSION['event_handler'])) {
            // 返回
            return;
        }
        // 获取事件处理类
        $eventHandler = $_SESSION['event_handler'];
        // 如果不存在事件处理类，则直接关闭连接
        if(!class_exists($eventHandler)) {
            // 返回
            return;
        }
        // 如果事件处理类方法存在
        if (is_callable($eventHandler . '::onClose')) {
            // 执行事件处理类方法
            call_user_func($eventHandler . '::onClose', $clientId);
        }
    }

    /**
     * 解析事件处理类
     * @access public
     * @param string $requestUri
     * @return string
     */
    protected static function parseEventHandler($requestUri)
    {
        // 获取UrlPath
        $urlPath = parse_url($requestUri, PHP_URL_PATH);
        // 替换为斜杠
        $urlPath = str_replace(['.', '\\', ' '], ['/', '/', ''], $urlPath);
        // 去掉重复的斜杠
        $urlPath = preg_replace('#\/+#', '/', $urlPath);
        // 如果为空或者以斜杠结尾
        if (empty($urlPath) || substr($urlPath, -1) == '/') {
            // 追加Index
            $urlPath .= 'Index';
        }
        // 去掉前后的斜杠
        $handler = trim($urlPath, '/');
        // 如果包含了分隔符
        if(false !== strpos($handler, '/')){
            // 获取分隔符位置
            $pos = strrpos($handler, '/') + 1;
            // 获取命名空间
            $namespace = str_replace('/', '\\', substr($handler, 0, $pos));
            // 获取事件处理类名
            $handler = $namespace . Str::studly(substr($handler, $pos));
        }
        // 没有包含分隔符
        else {
            // 直接设置为处理类
            $handler = Str::studly($handler);
        }
        // 去掉标签
        $handler = strip_tags($handler);
        // 返回带命名空间的事件处理类名
        return rtrim(static::$namespace, '\\') . '\\' . $handler;
    }
}