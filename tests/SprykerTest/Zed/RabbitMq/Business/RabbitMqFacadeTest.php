<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\RabbitMq\Business;

use Codeception\Test\Unit;
use Spryker\Zed\RabbitMq\Business\Model\Queue\QueueInfo;
use Spryker\Zed\RabbitMq\Business\RabbitMqBusinessFactory;
use Spryker\Zed\RabbitMq\Business\RabbitMqFacade;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group RabbitMq
 * @group Business
 * @group Facade
 * @group RabbitMqFacadeTest
 * Add your own group annotations below this line
 */
class RabbitMqFacadeTest extends Unit
{
    /**
     * @var \Spryker\Zed\RabbitMq\Business\RabbitMqFacade
     */
    protected $rabbitMqFacade;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\RabbitMq\Business\RabbitMqBusinessFactory
     */
    protected $rabbitMqBusinessFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\RabbitMq\Business\Queue\QueueInfo
     */
    protected $queueInfoMock;

    /**
     * @return void
     */
    protected function _before()
    {
        $this->rabbitMqBusinessFactoryMock = $this->createMock(RabbitMqBusinessFactory::class);
        $this->queueInfoMock = $this->createMock(QueueInfo::class);

        $this->rabbitMqFacade = new RabbitMqFacade();
        $this->rabbitMqFacade->setFactory($this->rabbitMqBusinessFactoryMock);
    }

    /**
     * @return void
     */
    public function testAreQueuesEmpty()
    {
        $queueNames = ['queue1', 'queue2'];
        $expectedResult = true;

        $this->rabbitMqBusinessFactoryMock
            ->method('createQueueInfo')
            ->willReturn($this->queueInfoMock);

        $this->queueInfoMock
            ->method('areQueuesEmpty')
            ->with($queueNames)
            ->willReturn($expectedResult);

        $result = $this->rabbitMqFacade->areQueuesEmpty($queueNames);

        $this->assertEquals($expectedResult, $result);
    }
}
