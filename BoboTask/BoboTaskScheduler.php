<?php
/**
 * Created by PhpStorm.
 * User: wangyibo
 * Date: 7/14/16
 * Time: 15:27
 */

namespace Adocwang\Bbt;

class BoboTaskScheduler
{
    private $mq;
    private $logger;
    private $pidFile = '/var/run/bobo_task_scheduler';
    private $configArray = array();
    private $configPath;
    const sleep = 500000;


    public function __construct($configPath)
    {
        $this->setSignals();
        $this->configPath = $configPath;
        $this->config($this->configParser($this->configPath));
        $this->mq = new Mq($this->configArray['message_queue']);
        $this->logger = new Logger($this->configArray['logger']);
//        $this->signal();
    }

    public function configParser($path)
    {
        if (!file_exists($path)) {
            new BoboException('no config file find');
        }
        $content = file_get_contents($path);
        if (empty($content)) {
            new BoboException('no config file is empty');
        } else {
            $configArray = json_decode($content, true);
            if (empty($configArray)) {
                new BoboException('config is invalid  json');
            } else {
                return $configArray;
            }
        }
    }

    public function setSignals()
    {

        pcntl_signal(SIGHUP, function ($sigNum) {
            printf("The process has been reload.\n");
            Signal::set($sigNum);
        });

        pcntl_signal(SIGTERM, function ($sigNum) {
            printf("The process has been stoped.\n");
            Signal::set($sigNum);
        });

        pcntl_signal(SIGKILL, function ($sigNum) {
            printf("The process has been killed.\n");
            Signal::set($sigNum);
        });

    }

    private function daemon()
    {
        if (file_exists($this->pidFile)) {
            echo "The pid file $this->pidFile exists.\n";
            exit();
        }
        $this->initResources();
        $pid = pcntl_fork();
        if ($pid == -1) {
            die('could not fork');
        } else if ($pid) {
            exit($pid);
        } else {
            file_put_contents($this->pidFile, getmypid());
            return getmypid();
        }
    }

    private function initResources()
    {
        $this->mq = new Mq($this->configArray['message_queue']);
        $this->logger = new Logger($this->configArray['logger']);
    }

    public function config($configData, $value = "")
    {
        if (is_array($configData)) {
            foreach ($configData as $key => $value) {
                $this->setConfigKey($key, $value);
            }
            return true;
        } elseif (!empty($value)) {
            return $this->setConfigKey($configData, $value);
        } else {
            if (isset($this->configArray[$configData])) {
                return $this->configArray[$configData];
            } else {
                return null;
            }
        }
    }

    private function setConfigKey($key, $value)
    {
        if (isset($this->configArray[$key]) && is_array($this->configArray[$key])) {
            $this->configArray[$key] = array_merge_recursive($this->configArray[$key], $value);
        } else {
            $this->configArray[$key] = $value;
        }
//        if (strcmp($key, "task_key") === 0) {
//            $this->configArray['message_queue']['task_key'] = $value;
//            $this->configArray['logger']['task_key'] = $value;
//        }
        return $value;
    }

    public function run()
    {
        $this->loops();
    }


    /**
     * main loop of listening queues
     */
    protected function loops()
    {
        $looping = true;
        while ($looping) {
            pcntl_signal_dispatch();
            if (Signal::get() == SIGHUP) {
                $this->config($this->configParser($this->configPath));
                Signal::set(0);
            }
            foreach ($this->configArray['watching_tasks'] as $taskId => $watchingTask) {
                if (empty($watchingTask['task_key'])) {
                    continue;
                }
                if ($this->mq->count($watchingTask['task_key']) > 0) {
                    $this->runCommand($taskId);
                }
                //echo ' watch ' . $watchingTask['task_key'] . "|";
                //echo ".";
            }
            usleep(self::sleep);
        }
    }

    public function runCommand($taskId)
    {
        $taskInfo = $this->configArray['watching_tasks'][$taskId];
        $taskPsCmd = "ps -eo lstart,pid,command | grep '" . $taskInfo['script'] . "' | grep -v 'grep'";
        $taskResult = [];
        exec($taskPsCmd, $taskResult);
        if (count($taskResult) > 0) {
            return 2;
        }
        $phpCmd = "/usr/local/bin/php";
        $cmd = 'nohup ' . $phpCmd . " " . $taskInfo['script'] . ' > /dev/null';// 2>&1 & echo $!';
        //echo $cmd . "\n";
        $pid = trim(exec($cmd));
        $this->configArray['watching_tasks'][$taskId]['pid'] = $pid;
        return $pid;
    }

    private function start()
    {
        $this->daemon();
        printf("BoboTaskScheduler start\n");
        $this->run();
    }

    private function status()
    {
        $first = true;
        while (1) {
            if (file_exists($this->pidFile)) {
                $pid = file_get_contents($this->pidFile);
                if ($first) {
                    $first = false;
                    system("ps -eo pid,%cpu,%mem,lstart,etime,time,command " . $pid);
                } else {
                    system("ps -eo pid,%cpu,%mem,lstart,etime,time,command " . $pid . " | grep -v 'grep' | grep " . $pid);
                }
            }
        }
    }

    private function reload()
    {
        if (file_exists($this->pidFile)) {
            $pid = file_get_contents($this->pidFile);
            posix_kill($pid, SIGHUP);
        }
//        $this->stop();
//        $this->start();
    }

    private function help($proc)
    {
        printf("%s start | stop | restart | status | foreground | help \n", $proc);
    }

    private function stop()
    {
        if (file_exists($this->pidFile)) {
            $pid = file_get_contents($this->pidFile);
            posix_kill($pid, SIGKILL);
            unlink($this->pidFile);
            printf("BoboTaskScheduler stopped\n");
        }
    }

    public function main($argv)
    {

        if (count($argv) < 2) {
            $this->help($argv[0]);
            printf("please input help parameter\n");
            exit();
        }
        if ($argv[1] === 'stop') {
            $this->stop();
        } else if ($argv[1] === 'start') {
            $this->start();
        } else if ($argv[1] === 'reload') {
            $this->reload();
        } else if ($argv[1] === 'status') {
            $this->status();
        } else if ($argv[1] === 'reload') {
            $this->reload();
        } else {
            $this->help($argv[0]);
        }
    }
}