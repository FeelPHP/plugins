# Redis

- Remote Server/Client
- Memory based
- NoSQL

## Use 

- Cache
- List (pop/push) atomicity
- Store (persistence)

## Install

[Server Config](../ServerConfig.md)

## Start

```
$ redis-server redis.conf
$ redis-cli -h 127.0.0.1 -p 6379
127.0.0.1:6379> info
```

> redis.conf
> daemonize yes


## Data Structure

- String
- List
- Set
- Hash
- Sort Set

### String

```shell
> set
> get
> incr
> decrby
```

### List

```shell
> lpush
> rpop
> llen
```

> not unique

### Set

```shell
> sadd
> scard
> sismember
> srem
```

> - out of order
> - unique

### Hash

```shell
> hset
> hget
> hlen
> hmget
```

### Sort Set

```shell
> zadd
> zcard
> zrange zset1 0 2 withscores
> zrank zset1 val2
```

> value global unique
