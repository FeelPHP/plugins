# Config LNMP Server

## OS Linux

```shell
$ uname -a
Linux lnmp 4.8.0-22-generic #24-Ubuntu SMP Sat Oct 8 09:15:00 UTC 2016 x86_64 x86_64 x86_64 GNU/Linux
```


### Vim
```shell
$ sudo apt-get install vim
```

### Git

```shell
$ sudo apt-get install git
```

[Connecting to GitHub with SSH](https://help.github.com/articles/connecting-to-github-with-ssh/)


```shell
$ git config --global user.email ""
$ git config --global user.name ""

```

## Nginx

[Pre-Built Packages for Stable version](http://nginx.org/en/linux_packages.html)

### add key to `apt` program keyring
[nginx signing key](http://nginx.org/keys/nginx_signing.key)

```shell
$ wget http://nginx.org/keys/nginx_signing.key
$ sudo apt-key add nginx _signing.key
```

### append to `/etc/apt/sources.list`

```
deb http://nginx.org/packages/ubuntu/ yakkety nginx
deb-src http://nginx.org/packages/ubuntu/ yakkety nginx
```

### Install Nginx

```shell
$ sudo apt-get update
$ sudo apt-get install nginx
```

### Test

```shell
$ sudo nginx
```

## MySQL

[A Quick Guide to Using the MySQL APT Repository](https://dev.mysql.com/doc/mysql-apt-repo-quick-guide/en/)

### Adding the MySQL APT Repository

``` shell
$ wget https://dev.mysql.com/get/mysql-apt-config_0.8.3-1_all.deb
$ sudo dpkg -i mysql-apt-config_0.8.3-1_all.deb
$ sudo apt-get update
```

### Installing MySQL with APT

```shell
$ sudo apt-get install mysql-server
```

### Starting and Stopping the MySQL Server

```shell
$ sudo service mysql status
$ sudo service mysql stop
$ sudo service mysql start
```

## Redis

### Install
``` shell
$ wget http://download.redis.io/releases/redis-3.2.8.tar.gz
$ tar xzf redis-3.2.8.tar.gz
$ cd redis-3.2.8
$ make
$ make test
```
### Start

```shell
$ src/redis-server
$ src/redis-cli
> set foo bar
OK
> get foo
"bar:
```

## PHP

### Pre

```shell
$ sudo apt-get install libxml2*
$ sudo apt-get install libssl-dev
```

### Download

```shell
$ wget http://cn2.php.net/distributions/php-7.1.3.tar.gz
$ sudo tar zxf php-7.1.3.tar.gz
```

### Install

```shell
$ ./configure --enable-fpm --with-mysqli --with-openssl --with-pdo-mysql --enable-mbstring
$ sudo make
$ sudo make install
```

> `--with-mysql` option is no longer supported in PHP7.
> You need to use mysqli extension for this

### Config File

```shell
$ sudo cp php.ini-development /usr/local/php/php.ini
$ sudo cp /usr/local/etc/php-fpm.conf.default /usr/local/etc/php-fpm.conf
$ sudo cp sapi/fpm/php-fpm /usr/local/bin/
```

### if file doesn't exist, keep it away from php-fpm

```shell
$ sudo vim /usr/local/php/php.ini

cgi.fix-pathinfo=0
```

### `www-data` user

```shell
$ sudo vim /usr/local/etc/php-fpm.conf

include=/user/local/etc/php-fpm.d/*.conf

$ sudo cp /usr/local/etc/php.d/www.conf.default /usr/local/etc/php.d/www.conf
user = www-data
group = www-data

# start fpm
$ sudo /usr/local/bin/php-fpm
```

### Config Nginx to process PHP

```shell
$ vim /usr/local/nginx/conf/nginx.conf

location / {
    root    html;
    index   index.php index.html index.htm;
}

# next step is to ensure that `.php` files are passed to the PHP-FPM backend
location ~* \.php$ {
    fastcgi_index     index.php;
    fastcgi_pass      127.0.0.1:9000;
    include           fastcgi_params;
    fastcgi_param     SCRIPT_FILENAME    $document_root$fastcgi_script_name;
    fastcgi_param     SCRIPT_NAME        $fastcgi_script_name;
}
```
> `$document_root` is web dir

### Restart Nginx

```shell
$ sudo /etc/init.d/nginx stop
$ sudo /etc/init.d/nginx start
```
> ps -aux | grep nginx
> sudo kill -9 port
