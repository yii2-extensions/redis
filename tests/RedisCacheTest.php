<?php

declare(strict_types=1);

namespace yiiunit\extensions\redis;

use yii\redis\Cache;
use yii\redis\Connection;

/**
 * Class for testing redis cache backend
 *
 * @group redis
 * @group caching
 */
class RedisCacheTest extends CacheTestCase
{
    private Cache|null $_cacheInstance = null;

    /**
     * @return Cache
     */
    protected function getCacheInstance()
    {
        $databases = TestCase::getParam('databases');
        $params = $databases['redis'] ?? null;
        if ($params === null) {
            $this->markTestSkipped('No redis server connection configured.');
        }
        $connection = new Connection($params);
//        if (!@stream_socket_client($connection->hostname . ':' . $connection->port, $errorNumber, $errorDescription, 0.5)) {
//            $this->markTestSkipped('No redis server running at ' . $connection->hostname . ':' . $connection->port . ' : ' . $errorNumber . ' - ' . $errorDescription);
//        }

        $this->mockApplication(['components' => ['redis' => $connection]]);

        if ($this->_cacheInstance === null) {
            $this->_cacheInstance = new Cache();
        }

        return $this->_cacheInstance;
    }

    protected function resetCacheInstance()
    {
        $this->getCacheInstance()->redis->flushdb();
        $this->_cacheInstance = null;
    }

    public function testExpireMilliseconds(): void
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->set('expire_test_ms', 'expire_test_ms', 0.2));
        usleep(100000);
        $this->assertEquals('expire_test_ms', $cache->get('expire_test_ms'));
        usleep(300000);
        $this->assertFalse($cache->get('expire_test_ms'));
    }

    public function testExpireAddMilliseconds(): void
    {
        $cache = $this->getCacheInstance();

        $this->assertTrue($cache->add('expire_testa_ms', 'expire_testa_ms', 0.2));
        usleep(100000);
        $this->assertEquals('expire_testa_ms', $cache->get('expire_testa_ms'));
        usleep(300000);
        $this->assertFalse($cache->get('expire_testa_ms'));
    }

    /**
     * Store a value that is 2 times buffer size big
     * https://github.com/yiisoft/yii2/issues/743
     */
    public function testLargeData(): void
    {
        $cache = $this->getCacheInstance();

        $data = str_repeat('XX', 8192); // https://www.php.net/manual/en/function.fread.php
        $key = 'bigdata1';

        $this->assertFalse($cache->get($key));
        $cache->set($key, $data);
        $this->assertSame($cache->get($key), $data);

        // try with multibyte string
        $data = str_repeat('ЖЫ', 8192); // https://www.php.net/manual/en/function.fread.php
        $key = 'bigdata2';

        $this->assertFalse($cache->get($key));
        $cache->set($key, $data);
        $this->assertSame($cache->get($key), $data);
    }

    /**
     * Store a megabyte and see how it goes
     * https://github.com/yiisoft/yii2/issues/6547
     */
    public function testReallyLargeData(): void
    {
        $cache = $this->getCacheInstance();

        $keys = [];
        for ($i = 1; $i < 16; $i++) {
            $key = 'realbigdata' . $i;
            $data = str_repeat('X', 100 * 1024); // 100 KB
            $keys[$key] = $data;

//            $this->assertTrue($cache->get($key) === false); // do not display 100KB in terminal if this fails :)
            $cache->set($key, $data);
        }
        $values = $cache->multiGet(array_keys($keys));
        foreach ($keys as $key => $value) {
            $this->assertArrayHasKey($key, $values);
            $this->assertSame($values[$key], $value);
        }
    }

    public function testMultiByteGetAndSet(): void
    {
        $cache = $this->getCacheInstance();

        $data = ['abc' => 'ежик', 2 => 'def'];
        $key = 'data1';

        $this->assertFalse($cache->get($key));
        $cache->set($key, $data);
        $this->assertSame($cache->get($key), $data);
    }

    public function testReplica(): void
    {
        $this->resetCacheInstance();

        $cache = $this->getCacheInstance();
        $cache->enableReplicas = true;

        $key = 'replica-1';
        $value = 'replica';

        //No Replica listed
        $this->assertFalse($cache->get($key));
        $cache->set($key, $value);
        $this->assertSame($cache->get($key), $value);

        $databases = TestCase::getParam('databases');
        $redis = $databases['redis'] ?? null;

        $cache->replicas = [
            [
                'hostname' => $redis['hostname'] ?? 'localhost',
                'password' => $redis['password'] ?? null,
            ],
        ];
        $this->assertSame($cache->get($key), $value);

        //One Replica listed
        $this->resetCacheInstance();
        $cache = $this->getCacheInstance();
        $cache->enableReplicas = true;
        $cache->replicas = [
            [
                'hostname' => $redis['hostname'] ?? 'localhost',
                'password' => $redis['password'] ?? null,
            ],
        ];
        $this->assertFalse($cache->get($key));
        $cache->set($key, $value);
        $this->assertSame($cache->get($key), $value);

        //Multiple Replicas listed
        $this->resetCacheInstance();
        $cache = $this->getCacheInstance();
        $cache->enableReplicas = true;

        $cache->replicas = [
            [
                'hostname' => $redis['hostname'] ?? 'localhost',
                'password' => $redis['password'] ?? null,
            ],
            [
                'hostname' => $redis['hostname'] ?? 'localhost',
                'password' => $redis['password'] ?? null,
            ],
        ];
        $this->assertFalse($cache->get($key));
        $cache->set($key, $value);
        $this->assertSame($cache->get($key), $value);

        //invalid config
        $this->resetCacheInstance();
        $cache = $this->getCacheInstance();
        $cache->enableReplicas = true;

        $cache->replicas = ['redis'];
        $this->assertFalse($cache->get($key));
        $cache->set($key, $value);
        $this->assertSame($cache->get($key), $value);

        $this->resetCacheInstance();
    }

    public function testFlushWithSharedDatabase(): void
    {
        $instance = $this->getCacheInstance();
        $instance->shareDatabase = true;
        $instance->keyPrefix = 'myprefix_';
        $instance->redis->set('testkey', 'testvalue');

        for ($i = 0; $i < 1000; $i++) {
            $instance->set(sha1((string) $i), uniqid('', true));
        }
        $keys = $instance->redis->keys('*');
        $this->assertCount(1001, $keys);

        $instance->flush();

        $keys = $instance->redis->keys('*');
        $this->assertCount(1, $keys);
        $this->assertSame(['testkey'], $keys);
    }
}
