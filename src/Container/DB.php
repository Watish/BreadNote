<?php

namespace App\Container;

use Mix\Database\Database;

class DB
{

    /**
     * @var Database
     */
    private static $instance;

    public static function init(): void
    {
        $config = json_decode(file_get_contents(BASE_DIR.'conf/config.json'),true);
        $dsn = $config['DATABASE_DSN'];
        $username = $config['DATABASE_USERNAME'];
        $password = $config['DATABASE_PASSWORD'];
        $db = new Database($dsn, $username, $password);
        APP_DEBUG and $db->setLogger(new DBLogger());
        self::$instance = $db;
    }

    /**
     * @return Database
     */
    public static function instance(): Database
    {
        if (!isset(self::$instance)) {
            static::init();
        }
        return self::$instance;
    }

    public static function enableCoroutine()
    {
        $maxOpen = 30;        // 最大开启连接数
        $maxIdle = 10;        // 最大闲置连接数
        $maxLifetime = 3600;  // 连接的最长生命周期
        $waitTimeout = 0.0;   // 从池获取连接等待的时间, 0为一直等待
        self::instance()->startPool($maxOpen, $maxIdle, $maxLifetime, $waitTimeout);
        \Swoole\Runtime::enableCoroutine(); // 必须放到最后，防止触发协程调度导致异常
    }

}
