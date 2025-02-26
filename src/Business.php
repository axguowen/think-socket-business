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

use think\App;
use think\console\Output;
use think\console\Input;
use Workerman\Worker;
use GatewayWorker\BusinessWorker;

class Business
{
    /**
     * 配置参数
     * @var array
     */
	protected $options = [
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
        // 内容输出文件路径
        'stdout_file' => '',
        // pid文件路径
        'pid_file' => '',
        // 日志文件路径
        'log_file' => '',
	];

    /**
     * 架构函数
     * @access public
     * @param App $app 容器实例
     * @return void
     */
    public function __construct(App $app)
    {
        // 记录容器实例
        $this->app = $app;
        // 合并配置
		$this->options = array_merge($this->options, $this->app->config->get('socketbusiness', []));
        // 初始化
		$this->init();
    }

    /**
     * 初始化
     * @access protected
	 * @return void
     */
	protected function init()
	{
        // 如果注册地址为空
        if(empty($this->options['register_address'])){
            // 抛出异常
            throw new \Exception('register_address can not be empty');
        }
        // 如果业务处理类是空
        if(empty($this->options['event_handler'])){
            throw new \Exception('business event handler can not be empty');
        }
	}

    /**
     * 启动
     * @access public
     * @param Input $input 输入
     * @param Output $output 输出
	 * @return void
     */
	public function start(Input $input, Output $output)
	{
        // 不是控制台模式
        if (!$this->app->runningInConsole()) {
            // 抛出异常
            throw new \Exception('only supports running in cli mode');
        }

        // 如果是守护进程模式
        if ($input->hasOption('daemon')) {
            // 修改配置为守护进程模式
            $this->options['daemonize'] = true;
        }

        // 进程名称为空
		if(empty($this->options['name'])){
            $this->options['name'] = 'think-socket-business';
        }
        // 构造新的运行时目录
		$runtimePath = $this->app->getRuntimePath() . $this->options['name'] . DIRECTORY_SEPARATOR;
        // 设置runtime路径
        $this->app->setRuntimePath($runtimePath);

        // 主进程reload
		Worker::$onMasterReload = function () {
			// 清理opcache
            if (function_exists('opcache_get_status')) {
                if ($status = opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        // 内容输出文件路径
		if(!empty($this->options['stdout_file'])){
			// 目录不存在则自动创建
			$stdout_dir = dirname($this->options['stdout_file']);
			if (!is_dir($stdout_dir)){
				mkdir($stdout_dir, 0755, true);
			}
			// 指定stdout文件路径
			Worker::$stdoutFile = $this->options['stdout_file'];
		}
		// pid文件路径
		if(empty($this->options['pid_file'])){
			$this->options['pid_file'] = $runtimePath . 'worker' . DIRECTORY_SEPARATOR . $this->options['name'] . '.pid';
		}

		// 目录不存在则自动创建
		$pid_dir = dirname($this->options['pid_file']);
		if (!is_dir($pid_dir)){
			mkdir($pid_dir, 0755, true);
		}
		// 指定pid文件路径
		Worker::$pidFile = $this->options['pid_file'];

        // 日志文件路径
		if(empty($this->options['log_file'])){
			$this->options['log_file'] = $runtimePath . 'worker' . DIRECTORY_SEPARATOR . $this->options['name'] . '.log';
		}
		// 目录不存在则自动创建
		$log_dir = dirname($this->options['log_file']);
		if (!is_dir($log_dir)){
			mkdir($log_dir, 0755, true);
		}
		// 指定日志文件路径
		Worker::$logFile = $this->options['log_file'];

        // 如果指定以守护进程方式运行
        if (true === $this->options['daemonize']) {
            Worker::$daemonize = true;
        }

        // BussinessWorker 进程
        $businessWorker = new BusinessWorker();
        // worker名称
        $businessWorker->name = $this->options['name'];
        // BussinessWorker进程数量
        $businessWorker->count = $this->options['count'];
        // 服务注册地址
        $businessWorker->registerAddress = $this->options['register_address'];
        // Gateway通讯密钥
        $businessWorker->secretKey = $this->options['secret_key'];
        // 业务处理类
        $businessWorker->eventHandler = $this->options['event_handler'];

        // 启动
		Worker::runAll();
	}

    /**
     * 停止
     * @access public
     * @return void
     */
    public function stop()
    {
        Worker::stopAll();
    }
}
