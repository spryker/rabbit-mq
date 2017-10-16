<?php

/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Business\Model\Exchange;

use PHPUnit_Framework_TestCase;
use Spryker\Client\Queue\Model\Adapter\AdapterInterface;
use Spryker\Client\RabbitMq\Model\RabbitMqAdapter;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Exchange;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group Business
 * @group Model
 * @group Exchange
 * @group ExchangeTest
 * Add your own group annotations below this line
 */
class ExchangeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider exchangeDeleteDataProvider
     *
     * @param array $exchangeNames
     * @param array $exchangeBlacklist
     * @param array $consecutiveCallArguments
     *
     * @return void
     */
    public function testBlacklistedExchangeNamesShouldNotBeDeleted(array $exchangeNames, array $exchangeBlacklist, array $consecutiveCallArguments)
    {
        $exchangeInfoMock = $this->createExchangeInfoMock($exchangeNames);
        $adapterMock = $this->createAdapterMock($consecutiveCallArguments);
        $exchange = new Exchange($exchangeInfoMock, $adapterMock, $exchangeBlacklist);

        $exchange->deleteAllExchanges();
    }

    /**
     * @return array
     */
    public function exchangeDeleteDataProvider()
    {
        return [
            [
                ['amq.foo', 'foo'],
                ['/^amq./'],
                ['foo'],
            ],
            [
                ['amq.foo', 'foo'],
                ['foo'],
                ['amq.foo'],
            ],
        ];
    }

    /**
     * @param array $exchangeNames
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Zed\RabbitMq\Business\Model\Exchange\ExchangeInfoInterface
     */
    protected function createExchangeInfoMock(array $exchangeNames)
    {
        $exchangeInfoMock = $this->getMockBuilder(ExchangeInfoInterface::class)->setMethods(['getAllExchangeNames'])->getMock();
        $exchangeInfoMock->expects($this->once())->method('getAllExchangeNames')->willReturn($exchangeNames);

        return $exchangeInfoMock;
    }

    /**
     * @param array $consecutiveCallArguments
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Spryker\Client\Queue\Model\Adapter\AdapterInterface
     */
    protected function createAdapterMock(array $consecutiveCallArguments)
    {
        $adapterMock = $this->getMockBuilder(RabbitMqAdapter::class)->setMethods(['deleteExchange'])->disableOriginalConstructor()->getMock();
        $consecutive = [];
        foreach ($consecutiveCallArguments as $consecutiveCallArgument) {
            $consecutive[] = $this->equalTo($consecutiveCallArgument);
        };
        $adapterMock->expects($this->any())->method('deleteExchange')->withConsecutive($consecutive);

        return $adapterMock;
    }
}
