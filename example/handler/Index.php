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

namespace app\example\handler;

use GatewayWorker\Lib\Gateway;

class Index extends Base
{
    /**
     * 客户端连接上gateway完成websocket握手时触发的回调
     * @param string $clientId
     * @param array $data
     * @return void
     */
    public static function onWebSocketConnect(string $clientId, array $data)
    {
        // 向当前clientId发送数据 
        Gateway::sendToClient($clientId, "Hello $clientId\r\n");
        // 向所有人发送
        Gateway::sendToAll("$clientId login\r\n");
    }

    /**
     * 当客户端发来消息时触发
     * @param string $clientId 连接id
     * @param mixed $message 具体消息
     * @return void
     */
    public static function onMessage($clientId, $message)
    {
        // 向所有人发送 
        Gateway::sendToAll("$clientId said $message\r\n");
    }
   
    /**
     * 当用户断开连接时触发
     * @param string $clientId 连接id
     * @return void
     */
    public static function onClose($clientId)
    {
        // 向所有人发送 
        GateWay::sendToAll("$clientId logout\r\n");
    }
}