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

use think\console\Command as Base;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;

class Command extends Base
{
    /**
     * 配置
     * @access protected
     * @return void
     */
    protected function configure()
    {
        // 指令配置
        $this->setName('socket:business')
            ->addArgument('action', Argument::OPTIONAL, 'start|stop|restart|reload|status', 'start')
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the socket business service in daemon mode.')
            ->setDescription('Socket Business Service For ThinkPHP');
    }

    /**
     * 执行命令
     * @access protected
     * @param Input $input 输入
     * @param Output $output 输出
     * @return void
     */
    protected function execute(Input $input, Output $output)
    {
        // 获取命令行参数
        global $argv;
        // 如果是入口文件是think
        if (isset($argv[0]) && $argv[0] == 'think') {
            // 移除think
            array_shift($argv);
            // 追加当前目录的start.php
            array_unshift($argv, __DIR__ . '/start.php');
            // 构造新命令
            $command = sprintf('%s %s', PHP_BINARY, implode(' ', $argv));
            // 执行命令
            passthru($command);
            // 返回
            return false;
        }
        // 获取参数
        $action = $input->getArgument('action');
        // 如果是linux系统
        if (DIRECTORY_SEPARATOR !== '\\') {
            if (!in_array($action, ['start', 'stop', 'reload', 'restart', 'status'])) {
                $output->writeln('<error>Invalid argument action:' . $action . ', Expected start|stop|restart|reload|status.</error>');
                return false;
            }
            // 移除命令行参数中的think
            array_shift($argv);
        }
        // windows只支持start方法
        elseif ('start' != $action) {
            $output->writeln('<error>Not Support action:' . $action . ' on Windows.</error>');
            return false;
        }

        // 如果是启动
        if ('start' == $action) {
            $output->writeln('Starting socket business service...');
        }

        // 实例化
        $business = $this->app->make(Business::class);

        if (DIRECTORY_SEPARATOR == '\\') {
            $output->writeln('You can exit with <info>`CTRL-C`</info>');
        }

        // 启动
		$business->start($input, $output);
    }
}