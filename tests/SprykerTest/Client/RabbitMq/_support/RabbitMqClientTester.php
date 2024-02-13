<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerTest\Client\RabbitMq;

use Codeception\Actor;
use Codeception\Stub;
use Generated\Shared\Transfer\QueueConnectionTransfer;
use Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface;
use Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface;
use Spryker\Client\RabbitMq\RabbitMqConfig;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(\PHPMD\PHPMD)
 */
class RabbitMqClientTester extends Actor
{
    use _generated\RabbitMqClientTesterActions;

    /**
     * @var array
     */
    public const AVAILABLE_LOCALES = ['de_DE', 'en_US'];

    /**
     * @api
     *
     * @return bool
     */
    public function isDynamicStoreEnabled(): bool
    {
        return (bool)getenv('SPRYKER_DYNAMIC_STORE_MODE');
    }

    /**
     * @return \Generated\Shared\Transfer\QueueConnectionTransfer
     */
    public function createQueueConnectionTransfer(): QueueConnectionTransfer
    {
        return Stub::makeEmpty(QueueConnectionTransfer::class);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Dependency\Client\RabbitMqToStoreClientInterface
     */
    public function createStoreClient(): RabbitMqToStoreClientInterface
    {
        return Stub::makeEmpty(RabbitMqToStoreClientInterface::class);
    }

    /**
     * @return \Spryker\Client\RabbitMq\RabbitMqConfig
     */
    public function createRabbitMqConfig(): RabbitMqConfig
    {
        return Stub::make(RabbitMqConfig::class, ['getDefaultLocaleCode' => 'en_US']);
    }

    /**
     * @return \Spryker\Client\RabbitMq\Model\Helper\QueueEstablishmentHelperInterface
     */
    public function createQueueEstablishmentHelper(): QueueEstablishmentHelperInterface
    {
        return Stub::makeEmpty(QueueEstablishmentHelperInterface::class);
    }
}
