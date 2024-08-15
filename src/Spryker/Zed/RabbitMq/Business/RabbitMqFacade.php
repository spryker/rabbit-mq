<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer;
use Psr\Log\LoggerInterface;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqBusinessFactory getFactory()
 */
class RabbitMqFacade extends AbstractFacade implements RabbitMqFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function purgeAllQueues(LoggerInterface $logger)
    {
        return $this->getFactory()->createQueue()->purgeAllQueues($logger);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllQueues(LoggerInterface $logger)
    {
        return $this->getFactory()->createQueue()->deleteAllQueues($logger);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function deleteAllExchanges(LoggerInterface $logger)
    {
        return $this->getFactory()->createExchange()->deleteAllExchanges($logger);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return void
     */
    public function setupConnection(): void
    {
        $this->getFactory()->getConection()->setupQueuesAndExchanges();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return bool
     */
    public function setUserPermissions(LoggerInterface $logger)
    {
        return $this->getFactory()->createUserPermissionHandler()->setPermissions($logger);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\RabbitMqQueueCollectionTransfer
     */
    public function getQueues(): RabbitMqQueueCollectionTransfer
    {
        return $this->getFactory()->createQueueInfo()->getQueues();
    }
}
