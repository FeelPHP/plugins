<?php

// Hash 表中的元素指针个数，每个指针都是int，存储 Hash 链表的文件偏移量
define('DB_BUCKET_SIZE', 262144);

// 每条记录的key的长度
define('DB_KEY_SIZE', 128);

// 一条索引记录的长度
define('DB_INDEX_SIZE', DB_KEY_SIZE + 12);

// 返回码
define('DB_SUCCESS', 1);
define('DB_FAILURE', -1);
define('DB_KEY_EXISTS', -2);

class DB
{
    
    private $idx_fp;
    private $dat_fp;
    private $closed;
    
    /*
     * Description: Open Database
     * @param $pathName : Data File Path
     * @return mixed
     */
    public function open($pathName)
    {
        $idx_path = $pathName.'.idx';
        $dat_path = $pathName.'.dat';
        
        if (!file_exists($idx_path)) {
            $init = true;
            $mode = 'w+b';
        } else {
            $init = false;
            $mode = 'r+b';
        }
        
        $this->idx_fp = fopen($idx_path, $mode);
        
        if (!$this->idx_fp) {
            return DB_FAILURE;
        }
        
        if ($init) {
            // 把 0x00000000 转换成无符号长整型的二进制
            $elem = pack('L', 0x00000000);
            
            for ($index = 0; $index < DB_BUCKET_SIZE; $index++) {
                fwrite($this->idx_fp, $elem, 4);
            }
        }
        
        $this->dat_fp = fopen($dat_path, $mode);
        
        if (!$this->dat_fp) {
            return DB_FAILURE;
        }
        
        return DB_SUCCESS;
    }
    
    /*
     * Description: Times33 Hash Algorithm
     * @param $key
     * @return int
     */
    private function times33Hash($key)
    {
        $len = 8;
        $key = substr(md5($key), 0, $len);
        $hash = 0;
        
        for ($index = 0; $index < $len; $index++) {
            $hash += 33 * $hash + ord($key[$index]);
        }
        
        // ox7FFFFFFF : 一个十六进制的数是4bit
        return $hash & 0x7FFFFFFF;
    }
    
    /*
     * Description : insert data
     * @param $key
     * $param $value
     */
    public function add($key, $value)
    {
        $offset = ($this->times33Hash($key) % DB_BUCKET_SIZE) * 4;
        
        $idxoff = fstat($this->idx_fp);
        $idxoff = intval($idxoff['size']);
        
        $datoff = fstat($this->dat_fp);
        $datoff = intval($datoff['size']);
        
        $keylen = strlen($key);
        $vallen = strlen($value);
        if ($keylen > DB_KEY_SIZE) {
            return DB_FAILURE;
        }
        
        // 0 表示最后一个记录，该链表再无其他记录
        $block = pack('L', 0x00000000);
        // key value
        $block .= $key;
        // 如果键值的长度没有达到最大长度，则用0填充
        $space = DB_KEY_SIZE - $keylen;
        for($index = 0; $index < $space; $index++) {
            $block .= pack('C', $datoff);
        }
        // 数据在文件中的偏移量
        $block .= pack('L', $datoff);
        // 数据记录的长度
        $block .= pack('L', $vallen);
        // 尽管 SEEK_SET 是默认值，但是显示声明了就不怕以后官方会改变了-.-
        fseek($this->idx_fp, $offset, SEEK_SET);
        // 检测该key所对应的 hash 值是否存在了
        $pos = @unpack('L', fread($this->idx_fp, 4));
        $pos = $pos[1];
        
        // 如果key不存在
        if ($pos == 0) {
            fseek($this->idx_fp, $offset, SEEK_SET);
            fwrite($this->idx_fp, pack('L', $idxoff), 4);
            
            fseek($this->idx_fp, 0, SEEK_END);
            fwrite($this->idx_fp, $block, DB_INDEX_SIZE);
            
            fseek($this->dat_fp, 0, SEEK_END);
            fwrite($this->dat_fp, $value, $vallen);
            
            return DB_SUCCESS;
        }
        
        // 如果key存在
        $found = false;
        while ($pos) {
            fseek($this->idx_fp, $pos, SEEK_SET);
            $tmp_block = fread($this->idx_fp, DB_INDEX_SIZE);
            $cpkey = substr($tmp_block, 4, DB_KEY_SIZE);
            
            // $cpkey = substr($tmp_block, 4, DB_KEY_SIZE);
            if(!strncmp($cpkey, $key, $keylen)) {
                $dataoff = unpack('L', substr($tmp_block, DB_KEY_SIZE + 4, 4));
                $dataoff = $dataoff[1];
                $datalen = unpack('L', substr($tmp_block, DB_KEY_SIZE + 8, 4));
                $datalen = $datalen[1];
                $found = true;
                
                break;
            }
            
            $prev = $pos;
            $pos = @unpack('L', substr($tmp_block, 0, 4));
            $pos = $pos[1];
            
        }
        
        if ($found) {
            return DB_KEY_EXISTS;
        }
        
        fseek($this->idx_fp, $prev, SEEK_SET);
        fwrite($this->idx_fp, pack('L', $idxoff), 4);
        fseek($this->idx_fp, 0, SEEK_END);
        fwrite($this->idx_fp, $block, DB_INDEX_SIZE);
        fseek($this->dat_fp, 0, SEEK_END);
        fwrite($this->dat_fp, $value, $vallen);
        
        return DB_SUCCESS;
    }
    
    /*
     * Description 查询一条记录
     * @param $key
     */
    public function get($key)
    {
        // 计算偏移量
        // key 的 hash 值对索引文件的大小求模再乘以4
        // 因为每个链表指针文件大小为4
        $offset = ($this->times33Hash($key) % DB_BUCKET_SIZE) * 4;
        
        // SEET_SET is default
        fseek($this->idx_fp, $offset, SEEK_SET);
        $pos = unpack('L', fread($this->idx_fp, 4));
        $pos = $pos[1];
        
        $found = false;
        while ($pos) {
            fseek($this->idx_fp, $pos, SEEK_SET);
            $block = fread($this->idx_fp, DB_INDEX_SIZE);
            $cpkey = substr($block, 4, DB_KEY_SIZE);
            
            if (!strncmp($key, $cpkey, strlen($key))) {
                $dataoff = unpack('L', substr($block, DB_KEY_SIZE + 4, 4));
                $dataoff = $dataoff[1];
                
                $datalen = unpack('L', substr($block, DB_KEY_SIZE + 8, 4));
                $datalen = $datalen[1];
                
                $found = true;
                
                break;
            }
            
            $pos = unpack('L', substr($block, 0, 4));
            $pos = $pos[1];
            
        }
        
        if (!$found) {
            return null;
        }
        
        fseek($this->dat_fp, $dataoff, SEEK_SET);
        $data = fread($this->dat_fp, $datalen);
        
        return $data;
        
    }
    
    /*
     * Description 删除
     * @param $key
     */
    public function delete($key)
    {
        $offset = ($this->times33Hash($key) % DB_BUCKET_SIZE) * 4;
        fseek($this->idx_fp, $offset, SEEK_SET);
        $head = unpack('L', fread($this->idx_fp, 4));
        $head = $head[1]; 
        $curr = $head;
        $prev = 0;
        $found = false;
        while ($curr) {
            fseek($this->idx_fp, $curr, SEEK_SET);
            $block = fread($this->idx_fp, DB_INDEX_SIZE);
            
            $next = unpack('L', substr($block, 0, 4));
            $next = $nextp[1];
            
            $cpkey = substr($block, 4, DB_KEY_SIZE);
            if (!strncmp($key, $cpkey, strlen($key))) {
                $found = true;
                break;
            }
            $prev = $curr;
            $curr = $next;
        }
        
        if ($prev == 0) {
            fseek($this->idx_fp, $offset, SEEK_SET);
            fwrite($this->idx_fp, pack('L', $next), 4);
        } else {
            fseek($this->idx_fp, $prev, SEEK_SET);
            fwrite($this->idx_fp, pack('L', $next), 4);
        }
        
        
        return DB_SUCCESS;
    }
    
    public function close()
    {
        
        if(!$this->closed) {
            
            fclose($this->idx_fp);
            fclose($this->dat_fp);
            $this->closed = true;
        }
        
    }
    
}
