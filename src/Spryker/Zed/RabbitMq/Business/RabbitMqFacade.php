<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\RabbitMq\Business;

use Psr\Log\LoggerInterface;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \Spryker\Zed\RabbitMq\Business\RabbitMqBusinessFactory getFactory()
 */
class RabbitMqFacade extends AbstractFacade implements RabbitMqFacadeInterface
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
}
