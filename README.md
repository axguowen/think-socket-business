# ThinkPHP Socket Business 服务

一个简单的ThinkPHP Socket扩展中的Business服务
本服务主要负责业务处理

## 安装

~~~
composer require axguowen/think-socket-business
~~~

## 配置

首先配置config目录下的socketbusiness.php配置文件。
配置项说明：

~~~php
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
    'event_handler' => '',
    // 是否以守护进程启动
    'daemonize' => false,
];
~~~

## 启动停止

定时任务的启动停止均在命令行控制台操作，所以首先需要在控制台进入tp目录

### 启动命令

~~~
php think socket:business start
~~~

要使用守护进程模式启动可以将配置项deamonize设置为true
或者在启动命令后面追加 -d 参数，如下：
~~~
php think socket:business start -d
~~~

### 停止
~~~
php think socket:business stop
~~~

### 查看进程状态
~~~
php think socket:business status
~~~

## 注意
Windows下不支持多进程设置，也不支持守护进程方式运行，正式生产环境请用Linux