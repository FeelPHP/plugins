# Database

- File Store
- NoSQL

Read/Write with File

## Store File

### Index File

- Part I

all the pointers of the second part
所有的索引指针是记录所有相同Hash值的key的指针，它是一个链表结构，记录在数据文件的位置和同key的下一个值。

- Part II

index record
每条记录有四部分：
- 4Bytes 下一条索引的偏移量
- 该记录的key 128Bytes
- 数据偏移量 4Bytes
- 数据记录长度 4Bytes

设定文件存储上限: 262144 个

### Data File

## 查找流程

1. 根据key算出hash值，获取该hash值的链表在索引文件第一部分（所有指针区）的位置
2. 根据上一步获取的位置，获取值，时间复杂度O(1)
3. 根据步骤一的值，找到索引文件中第二部分（索引记录）的位置，也就是和key相同hash值的所有指针的链表
   顺着链表查找该key，获取该key在链表中存放的数据，数据只包含该key在索引文件中的位置，时间复杂度为O(n)
4. 根据步骤二所获取的key在索引文件位置，得到索引文件中存放该key的信息。信息包含在真实数据文件中存放真实数据的位置。
5. 根据步骤四所获取的位置，在真实数据文件中获取数据，并返回给应用程序。


## 测试结果
插入 10000 条耗时：793ms
查找 10000 条耗时：149ms

## 实现方法
- 插入
- 查找
- 删除

《PHP核心技术与最佳实践》

## Bugs

### Permission Denied

The directory of data file must be writable.
