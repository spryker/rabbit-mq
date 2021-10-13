<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq\Model\Connection;

use Codeception\Test\Unit;
use Spryker\Client\RabbitMq\Model\Exception\ConnectionConfigIsNotDefinedException;
use Spryker\Client\RabbitMq\Model\Exception\DefaultConnectionNotFoundException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group RabbitMq
 * @group Model
 * @group Connection
 * @group ConnectionManagerTest
 * Add your own group annotations below this line
 */
class ConnectionManagerTest extends Unit
{
    /**
     * @var string
     */
    protected const STORE_NAME = 'DE';
    /**
     * @var string
     */
    protected const LOCALE_CODE = 'en_US';
    /**
     * @var string
     */
    protected const QUEUE_POOL_NAME = 'synchronizationPool';

    /**
     * @var string
     */
    protected const INCORRECT_LOCALE_CODE = 'INCORRECT_LOCALE_CODE';
    /**
     * @var string
     */
    protected const INCORRECT_CONNECTION_NAME = 'INCORRECT_CONNECTION_NAME';

    /**
     * @var \SprykerTest\Client\RabbitMq\RabbitMqClientTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testGetChannelsByQueuePoolNameShouldThrowConnectionConfigIsNotDefinedException(): void
    {
        $this->expectException(ConnectionConfigIsNotDefinedException::class);

        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnectionAndIncorrectLocaleCode();

        $connectionManager->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, static::LOCALE_CODE);
    }

    /**
     * @return void
     */
    public function testGetDefaultChannelShouldThrowDefaultConnectionNotFoundException(): void
    {
        $this->expectException(DefaultConnectionNotFoundException::class);

        $connectionManager = $this->tester->createConnectionManagerWithoutDefaultQueueConnection();

        $connectionManager->getDefaultChannel();
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameForIncorrectLocaleCode(): void
    {
        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnection();

        $channels = $connectionManager
            ->getChannelsByStoreName(static::STORE_NAME, static::INCORRECT_LOCALE_CODE);

        $this->assertCount(0, $channels);
    }

    /**
     * @return void
     */
    public function testGetChannelsByQueuePoolNameWithoutLocaleCode(): void
    {
        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnection();

        $channels = $connectionManager->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, null);

        $this->assertCount(1, $channels);
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameForDefinedLocaleCode(): void
    {
        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnection();

        $channels = $connectionManager->getChannelsByStoreName(static::STORE_NAME, static::LOCALE_CODE);

        $this->assertCount(1, $channels);
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameWithoutLocaleCode(): void
    {
        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnection();

        $channels = $connectionManager->getChannelsByStoreName(static::STORE_NAME, null);

        $this->assertCount(1, $channels);
    }

    /**
     * @return void
     */
    public function testGetDefaultChannelShouldGetTheSameConnectionForMultipleCalls(): void
    {
        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnection();

        $firstChannelResult = $connectionManager->getDefaultChannel();
        $secondChannelResult = $connectionManager->getDefaultChannel();

        $this->assertSame($firstChannelResult, $secondChannelResult);
    }

    /**
     * @return void
     */
    public function testGetChannelsByStoreNameShouldGetTheSameConnectionForMultipleCalls(): void
    {
        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnection();

        $firstChannelsResult = $connectionManager->getChannelsByStoreName(static::STORE_NAME, null);
        $secondChannelsResult = $connectionManager->getChannelsByStoreName(static::STORE_NAME, null);

        $this->assertSame($firstChannelsResult, $secondChannelsResult);
    }

    /**
     * @return void
     */
    public function testGetChannelsByQueuePoolNameShouldGetTheSameConnectionForMultipleCalls(): void
    {
        $connectionManager = $this->tester->createConnectionManagerWithDefaultQueueConnection();

        $firstChannelsResult = $connectionManager
            ->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, static::LOCALE_CODE);
        $secondChannelsResult = $connectionManager
            ->getChannelsByQueuePoolName(static::QUEUE_POOL_NAME, static::LOCALE_CODE);

        $this->assertSame($firstChannelsResult, $secondChannelsResult);
    }
}
