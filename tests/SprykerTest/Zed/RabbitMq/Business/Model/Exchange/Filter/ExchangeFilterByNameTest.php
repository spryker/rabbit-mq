<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Zed\Business\Model\Exchange\Filter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\RabbitMqExchangeCollectionTransfer;
use Generated\Shared\Transfer\RabbitMqExchangeTransfer;
use Spryker\Zed\RabbitMq\Business\Model\Exchange\Filter\ExchangeFilterByName;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Business
 * @group Model
 * @group Exchange
 * @group Filter
 * @group ExchangeFilterByNameTest
 * Add your own group annotations below this line
 */
class ExchangeFilterByNameTest extends Unit
{
    /**
     * @return void
     */
    public function testFilterWhenBlacklistedByName(): void
    {
        $rabbitMyExchangeCollectionTransfer = new RabbitMqExchangeCollectionTransfer();
        $rabbitMqExchangeTransfer = new RabbitMqExchangeTransfer();
        $rabbitMqExchangeTransfer->setName('foo');
        $rabbitMyExchangeCollectionTransfer->addRabbitMqExchange($rabbitMqExchangeTransfer);

        $exchangeFilterByName = new ExchangeFilterByName(['foo']);
        $filteredExchangeCollectionTransfer = $exchangeFilterByName->filter($rabbitMyExchangeCollectionTransfer);

        $this->assertCount(0, $filteredExchangeCollectionTransfer->getRabbitMqExchanges());
    }

    /**
     * @return void
     */
    public function testFilterWHenBlacklistedByPattern(): void
    {
        $rabbitMyExchangeCollectionTransfer = new RabbitMqExchangeCollectionTransfer();
        $rabbitMqExchangeTransfer = new RabbitMqExchangeTransfer();
        $rabbitMqExchangeTransfer->setName('amq.foo');
        $rabbitMyExchangeCollectionTransfer->addRabbitMqExchange($rabbitMqExchangeTransfer);

        $exchangeFilterByName = new ExchangeFilterByName(['/^amq./']);
        $filteredExchangeCollectionTransfer = $exchangeFilterByName->filter($rabbitMyExchangeCollectionTransfer);

        $this->assertCount(0, $filteredExchangeCollectionTransfer->getRabbitMqExchanges());
    }

    /**
     * @return void
     */
    public function testDoesNotFilterWhenNotBlacklisted(): void
    {
        $rabbitMyExchangeCollectionTransfer = new RabbitMqExchangeCollectionTransfer();
        $rabbitMqExchangeTransfer = new RabbitMqExchangeTransfer();
        $rabbitMqExchangeTransfer->setName('foo');
        $rabbitMyExchangeCollectionTransfer->addRabbitMqExchange($rabbitMqExchangeTransfer);

        $exchangeFilterByName = new ExchangeFilterByName(['bar']);
        $filteredExchangeCollectionTransfer = $exchangeFilterByName->filter($rabbitMyExchangeCollectionTransfer);

        $this->assertCount(1, $filteredExchangeCollectionTransfer->getRabbitMqExchanges());
    }
}
