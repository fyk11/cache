<?php

namespace PFinal\Cache;
use Predis\Client;

/**
 * Redis缓存
 */
class Redis implements CacheInterface
{
    private $_cache;
    public $servers = array();
    public $hashKey = true;
    public $keyPrefix = '';

    public function __construct($config = array())
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }

        if (count($this->servers) > 0) {

            foreach ($this->servers as $server) {

                $options = array('replication' => true);
                $client = new Client($server, $options);
                $this->_cache=$client;

            }
        } else {
            $params = [
                'scheme' => 'tcp',
                'host' => '127.0.0.1',
                'port' => 6379,
            ];

            $client = new Client($params);
            $this->_cache=$client;
        }

    }

    /**
     * 增加一个条目到缓存服务器
     * 仅键名不存在的情况下，往缓存中存储值
     * @param string $key 要设置值的key
     * @param mixed $value 要存储的值，字符串和数值直接存储，其他类型序列化后存储
     * @param int $expire 当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间。
     * @return boolean 成功时返回 true， 或者在失败时返回 false. 如果这个key已经存在返回false
     */
    public function add($key, $value, $expire = 0)
    {
        $key = $this->generateUniqueKey($key);
        return $this->_cache->add($key, $value, MEMCACHE_COMPRESSED, $expire);
    }

    /**
     * 存放数据到缓存中
     * @param string $key 要设置值的key
     * @param mixed $value 要存储的值，字符串和数值直接存储，其他类型序列化后存储
     * @param int $expire 当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间
     * @return boolean 成功时返回 true， 或者在失败时返回 false.
     */
    public function set($key, $value, $expire = 0)
    {
        $expire = $expire > 0 ? $expire + time() : 0;

        $key = $this->generateUniqueKey($key);
        if (!$expire){
            return $this->_cache->set($key, $value);
        }
        return $this->_cache->setex($key,$expire,$value);
    }

    /**
     * 从服务端检回一个元素
     * @param $key string | array 要获取值的key或key数组
     * @return mixed 返回key对应的存储元素的字符串值或者在失败或key未找到的时候返回false
     */
    public function get($key)
    {
        $key = $this->generateUniqueKey($key);
        return $this->_cache->get($key);
    }

    /**
     * 从服务端检回多个匹配的元素
     * @param $keys array 要获取值的key或key数组
     * @return mixed 返回key对应的存储元素的字符串值或者在失败或key未找到的时候返回false
     */
    public function mget($keys)
    {
        if (!is_array($keys)) {
            return false;
        }
        $uids = array();
        foreach ($keys as $id) {
            $uids[$id]=$this->_cache->get($this->generateUniqueKey($id));
        }
        return $uids;
    }

    /**
     * 从服务端删除一个元素
     * @param string $key 要删除的元素的key
     * @return mixed 成功时返回 true， 或者在失败时返回 false.
     */
    public function delete($key)
    {
        $key=$this->generateUniqueKey($key);
        return $this->_cache->del($key);
    }

    /**
     * 清洗（删除）已经存储的所有的元素
     * 立即使所有已经存在的元素失效。
     * @return mixed 成功时返回 true， 或者在失败时返回 false
     */
    public function flush()
    {
        return $this->_cache-> flushdb ( ) ;
    }

    protected function generateUniqueKey($key)
    {
        return $this->hashKey ? md5($this->keyPrefix . $key) : $this->keyPrefix . $key;
    }
}