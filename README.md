# BreadNote

![avatar](/public/static/media/shortcut.png)

## 简介
BreadNote是一款在线轻量笔记编辑平台，在浏览器访问即可使用不需要本地安装，方便快捷。


## 技术栈
 `MixPHP`  `React` `Mysql`
#### 后端
由PHP编写，使用高性能的[MixPHP](https://github.com/mix-php/mix)框架提供restful规范的接口
#### 前端
由React编写，使用Ant Design，简约大气



## 安装

> 需要先安装 [Nginx](http://nginx.org/en/download.html) 、[PHP 7.4+](https://www.php.net/)、[MySQL 5.6+](https://www.mysql.com/)


## 数据库配置
配置文件 `/.env`

BreadNote不会自动创建数据库，所以我们要在配置前新建一个空的数据库

```
# APP
APP_DEBUG=true

# DATABASE
DATABASE_DSN='mysql:host=数据库地址;port=数据库端口;charset=utf8;dbname=数据库名'
DATABASE_USERNAME=管理员账号
DATABASE_PASSWORD=管理员密码

# REDIS
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_DATABASE=0
REDIS_PASSWORD=

# JWT
JWT_KEY=my_secret_key

```

#
## 快速开始

启动 PHP-FPM 开发服务

```
php -S 0.0.0.0:8000 /BreadNote文件夹地址/public/index.php
```

启动 [cli-server](https://www.php.net/manual/zh/features.commandline.webserver.php) 开发服务 (零依赖)

```
composer run-script --timeout=0 cliserver:start
```

启动 Swoole 多进程服务

```
composer run-script --timeout=0 swoole:start
```

启动 Swoole 协程服务

```
composer run-script --timeout=0 swooleco:start
```

启动 WorkerMan 多进程服务

```
composer run-script --timeout=0 workerman:start
```

## 执行脚本

- `composer run-script` 命令中的 `--timeout=0` 参数是防止 composer [执行超时](https://getcomposer.org/doc/06-config.md#process-timeout)
- `composer.json` 定义了命令执行脚本，对应上面的执行命令

```json
"scripts": {
    "cliserver:start": "php -S localhost:8000 public/index.php",
    "swoole:start": "php bin/swoole.php",
    "swooleco:start": "php bin/swooleco.php",
    "workerman:start": "php bin/workerman.php start",
    "cli:clearcache": "php bin/cli.php clearcache"
}
```

当然也可以直接下面这样启动，效果是一样的，但是 `scripts` 能帮你记录到底有哪些可用的命令，同时在IDE中调试更加方便。

```
php bin/swoole.php start
```


#


### 入口文件
骨架路径 `public/index.php`
```php
<?php
require __DIR__ . '/../vendor/autoload.php';

/**
 * PHP-FPM, cli-server 模式专用
 */

use App\Error;
use App\Vega;
use Dotenv\Dotenv;

Dotenv::createUnsafeImmutable(__DIR__ . '/../', '.env')->load();
define("APP_DEBUG", env('APP_DEBUG'));

Error::register();

return Vega::new()->run();
```

### 部署

和 Laravel、ThinkPHP 部署方法完全一致，将 `public/index.php` 在 `nginx` 配置 `rewrite` 重写即可

```
server {
    server_name www.domain.com;
    listen 80;
    root /data/project/public;
    index index.html index.php;

    location / {
        if (!-e $request_filename) {
            rewrite ^/(.*)$ /index.php/$1 last;
        }
    }

    location ~ ^(.+\.php)(.*)$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_split_path_info ^(.+\.php)(.*)$;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#

