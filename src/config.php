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

return [
    // Business进程名称, 方便status命令中查看统计
    'name' => 'think-socket-business',
    // BusinessWorker进程数量, 根据业务是否有阻塞式IO设置进程数为CPU核数的1倍-4倍即可。
    'count' => 2,
    // 注册服务地址, 格式类似于 '127.0.0.1:1236'。
    // 如果是部署了多个register服务则格式是数组，类似['192.168.0.1:1236','192.168.0.2:1236']
    'register_address' => '127.0.0.1:1236',
    // Gateway通讯密钥
    'secret_key' => '',
    // 业务处理类，业务类至少要实现onMessage静态方法，onConnect和onClose静态方法可以不用实现。
    'event_handler' => \think\socket\business\Events::class,
    // 是否以守护进程启动
    'daemonize' => false,
    // 内容输出文件路径
    'stdout_file' => '',
    // pid文件路径
    'pid_file' => '',
    // 日志文件路径
    'log_file' => '',
];
