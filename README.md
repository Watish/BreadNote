# BreadNote V2.0

## 简介
BreadNote是一款在线轻量笔记编辑平台，在浏览器访问即可使用不需要本地安装，方便快捷。


## 技术栈
`PHP` `MixPHP` `Swoole` `Javascript` `React` `Mysql` 
#### 后端
由PHP编写，使用国人编写的轻量级高性能框架MixPHP并利用swoole协程技术，提供restful规范的接口
#### 前端
由React编写，使用Ant Design，简约大气


## 预览图

![avatar](/public/static/media/shortcut.png)


## 安装

> 需要先安装 [Nginx](http://nginx.org/en/download.html) 、[PHP 7.4+](https://www.php.net/)、[MySQL 5.6+](https://www.mysql.com/)


## 数据库配置
配置文件 `/conf/config.json`

BreadNote不会自动创建数据库，所以我们要在使用前创建配置文件

```
{
    "DATABASE_DSN":"mysql:host=数据库地址;port=端口;charset=utf8;dbname=数据库名",
    "DATABASE_USERNAME":"数据库账号",
    "DATABASE_PASSWORD":"数据库密码"
}
```

#
## 快速开始

由于笔记项目使用了swoole协程服务，我们不再支持php-fpm的启动方式


启动 Swoole 协程服务

```
composer run-script --timeout=0 swoole:start
```


当然也可以直接下面这样启动，效果是一样的。

```
php bin/swoole.php start
```


#
## 使用Docker部署
需要先部署一个MySQL容器，或者已安装好MySQL数据库
```
docker run --name garbage -d -p 80:80 -v /path/to/config.json:/app/conf/config.json -v /tmp/garbage/:/app/cahce/ --restart=always watish/garbage:v2
```

### 配置文件映射
```
/path/to/config.json -> /app/conf/config.json
```
### *映射内存空间给BreadNote缓存目录
```
/tmp/BreadNote -> /app/cache
```
此方法可以有效防止笔记量增多后，或者高并发下的IO性能瓶颈，同时也能防止容器体积膨胀

#
##### 由于BreadNote使用swoole协程技术，我们不再需要浪费时间配置入口文件

