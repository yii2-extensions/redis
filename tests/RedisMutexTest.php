<?php

declare(strict_types=1);

namespace yiiunit\extensions\redis;

use Yii;
use yii\redis\Connection;
use yii\redis\Mutex;

/**
 * Class for testing redis mutex
 *
 * @group redis
 * @group mutex
 */
class RedisMutexTest extends TestCase
{
    protected static $mutexName = 'testmutex';

    protected static $mutexPrefix = 'prefix';

    private static array $_keys = [];

    public function testAcquireAndRelease(): void
    {
        $mutex = $this->createMutex();

        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();

        $this->assertTrue($mutex->acquire(static::$mutexName));
        $this->assertMutexKeyInRedis();
        $this->assertTrue($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();

        // Double release
        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();
    }

    public function testExpiration(): void
    {
        $mutex = $this->createMutex();

        $this->assertTrue($mutex->acquire(static::$mutexName));
        $this->assertMutexKeyInRedis();
        $this->assertLessThanOrEqual(1500, $mutex->redis->executeCommand('PTTL', [$this->getKey(static::$mutexName)]));

        sleep(2);

        $this->assertMutexKeyNotInRedis();
        $this->assertFalse($mutex->release(static::$mutexName));
        $this->assertMutexKeyNotInRedis();
    }

    /**
     * @covers \yii\redis\Mutex::acquireLock
     * @covers \yii\redis\Mutex::releaseLock
     *
     * @dataProvider \yiiunit\extensions\redis\providers\Data::acquireTimeout
     */
    public function testConcurentMutexAcquireAndRelease($timeout, $canAcquireAfterTimeout, $lockIsReleased): void
    {
        $mutexOne = $this->createMutex();
        $mutexTwo = $this->createMutex();

        $this->assertTrue($mutexOne->acquire(static::$mutexName));
        $this->assertFalse($mutexTwo->acquire(static::$mutexName));
        $this->assertTrue($mutexOne->release(static::$mutexName));
        $this->assertTrue($mutexTwo->acquire(static::$mutexName));

        if ($canAcquireAfterTimeout) {
            // Mutex 2 auto released the lock or it will be auto released automatically
            if ($lockIsReleased) {
                sleep($timeout);
            }
            $this->assertSame($lockIsReleased, !$mutexTwo->release(static::$mutexName));

            $this->assertTrue($mutexOne->acquire(static::$mutexName, $timeout));
            $this->assertTrue($mutexOne->release(static::$mutexName));
        } else {
            // Mutex 2 still holds the lock
            $this->assertMutexKeyInRedis();

            $this->assertFalse($mutexOne->acquire(static::$mutexName, $timeout));

            $this->assertTrue($mutexTwo->release(static::$mutexName));
            $this->assertTrue($mutexOne->acquire(static::$mutexName));
            $this->assertTrue($mutexOne->release(static::$mutexName));
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $databases = TestCase::getParam('databases');
        $params = $databases['redis'] ?? null;
        if ($params === null) {
            $this->markTestSkipped('No redis server connection configured.');

            return;
        }

        $connection = new Connection($params);
        $this->mockApplication(['components' => ['redis' => $connection]]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     *
     * @return Mutex
     */
    protected function createMutex()
    {
        return Yii::createObject([
            'class' => Mutex::className(),
            'expire' => 1.5,
            'keyPrefix' => static::$mutexPrefix,
        ]);
    }

    protected function getKey($name)
    {
        if (!isset(self::$_keys[$name])) {
            $mutex = $this->createMutex();
            $method = new \ReflectionMethod($mutex, 'calculateKey');
            $method->setAccessible(true);
            self::$_keys[$name] = $method->invoke($mutex, $name);
        }

        return self::$_keys[$name];
    }

    protected function assertMutexKeyInRedis()
    {
        $this->assertNotNull(Yii::$app->redis->executeCommand('GET', [$this->getKey(static::$mutexName)]));
    }

    protected function assertMutexKeyNotInRedis()
    {
        $this->assertNull(Yii::$app->redis->executeCommand('GET', [$this->getKey(static::$mutexName)]));
    }
}
